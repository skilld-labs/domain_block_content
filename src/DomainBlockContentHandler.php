<?php

namespace Drupal\domain_block_content;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\domain\DomainNegotiatorInterface;
use Drupal\domain_entity\DomainEntityMapper;

/**
 * Provides operations for domain block content module.
 */
class DomainBlockContentHandler {

  /**
   * The name of the access control field.
   */
  const FIELD_NAME = 'domain_block_parent';

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Domain negotiator.
   *
   * @var \Drupal\domain\DomainNegotiatorInterface
   */
  protected $domainNegotiator;

  /**
   * The Entity query.
   *
   * @var \Drupal\Core\Entity\Query\QueryInterface
   */
  protected $entityQuery;

  /**
   * Creates a new DomainEntityMapper object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\domain\DomainNegotiatorInterface $domain_negotiator
   *   The Domain negotiator.
   * @param \Drupal\Core\Entity\Query\QueryFactory $entity_query
   *   The Entity query.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, DomainNegotiatorInterface $domain_negotiator, QueryFactory $entity_query) {
    $this->entityTypeManager = $entity_type_manager;
    $this->domainNegotiator = $domain_negotiator;
    $this->entityQuery = $entity_query;
  }

  /**
   * Loads field storage config.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   *
   * @return \Drupal\field\Entity\FieldStorageConfig|null
   *   The field storage or NULL.
   */
  public function loadFieldStorage($entity_type_id) {
    $storage = $this->entityTypeManager->getStorage('field_storage_config');
    return $storage->load($entity_type_id . '.' . self::FIELD_NAME);
  }

  /**
   * Deletes field storage.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   */
  public function deleteFieldStorage($entity_type_id) {
    $field_storage = $this->loadFieldStorage($entity_type_id);
    if ($field_storage) {
      $field_storage->delete();
    }
  }

  /**
   * Delete domain fields.
   *
   * @param string $entity_type_id
   *   The entity type machine name.
   * @param string $bundle
   *   The entity type's bundle.
   */
  public function deleteField($entity_type_id, $bundle) {
    $field = $this->getRelationField($entity_type_id, $bundle);

    if ($field) {
      $field->delete();
    }
  }

  /**
   * Creates domain fields.
   *
   * @param string $entity_type_id
   *   The entity type machine name.
   * @param string $bundle
   *   The entity type's bundle.
   */
  public function addField($entity_type_id, $bundle) {
    $field_storage = $this->createFieldStorage($entity_type_id);
    $field_config_storage = $this->entityTypeManager->getStorage('field_config');
    $field = $this->getRelationField($entity_type_id, $bundle);

    if (empty($field)) {
      $field = [
        'label' => 'Domain block parent',
        'description' => 'Contains parent content block UUID.',
        'bundle' => $bundle,
        'required' => FALSE,
        'field_storage' => $field_storage,
      ];

      $field_config_storage->create($field)->save();
    }
  }

  /**
   * Return domain block parent field config.
   *
   * @param string $entity_type_id
   *   The entity type machine name.
   * @param string $bundle
   *   The entity type's bundle.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   Domain block parent field config if available or NULL otherwise.
   */
  public function getRelationField($entity_type_id, $bundle) {
    return $this->getField($entity_type_id, $bundle, self::FIELD_NAME);
  }

  /**
   * Return domain block domain entity field config.
   *
   * @param string $entity_type_id
   *   The entity type machine name.
   * @param string $bundle
   *   The entity type's bundle.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   Domain block parent field config if available or NULL otherwise.
   */
  public function getDomainField($entity_type_id, $bundle) {
    return $this->getField($entity_type_id, $bundle, DomainEntityMapper::FIELD_NAME);
  }

  /**
   * Return field config related to requested data set.
   *
   * @param string $entity_type_id
   *   The entity type machine name.
   * @param string $bundle
   *   The entity type's bundle.
   * @param string $field_name
   *   Field machine name.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   Domain block parent field config if available or NULL otherwise.
   */
  public function getField($entity_type_id, $bundle, $field_name) {
    return $this->entityTypeManager
      ->getStorage('field_config')
      ->load($entity_type_id . '.' . $bundle . '.' . $field_name);
  }

  /**
   * Creates field storage.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   *
   * @return \Drupal\field\Entity\FieldStorageConfig
   *   The field storage.
   */
  public function createFieldStorage($entity_type_id) {
    // Prevent creation of existing field storage.
    if ($field_storage = $this->loadFieldStorage($entity_type_id)) {
      return $field_storage;
    }

    $storage = $this->entityTypeManager->getStorage('field_storage_config');

    return $storage->create([
      'entity_type' => $entity_type_id,
      'field_name' => self::FIELD_NAME,
      'type' => 'string',
      'persist_with_no_fields' => TRUE,
      'locked' => TRUE,
      'cardinality' => 1,
    ])->save();
  }

  /**
   * Check is requested entity accessible for currently active domain.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $block_content
   *   Entity object.
   * @param string $uuid
   *   Parent entity UUID.
   *
   * @return bool
   *   Result of check.
   */
  public function isAccessibleForCurrentDomain(FieldableEntityInterface $block_content, $uuid) {

    if (!$this->isCorrectEntity($block_content)) {
      return TRUE;
    }

    $domains = $this->getEntityRelatedDomains($block_content);

    // If domains not selected - check is domain specific child available.
    if (empty($domains)) {
      return !$this->getBlockContentDomainChildId($uuid);
    }

    $current_domain_id = $this->domainNegotiator->getActiveId();
    return isset($domains[$current_domain_id]);
  }

  /**
   * Return list of all related domain IDs to the requested entity.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   Entity object.
   *
   * @return array
   *   List of all related domain IDs.
   */
  public function getEntityRelatedDomains(FieldableEntityInterface $entity) {
    $domains = [];

    if (!$entity->hasField(DomainEntityMapper::FIELD_NAME)) {
      return $domains;
    }

    $list = $entity->get(DomainEntityMapper::FIELD_NAME);

    foreach ($list as $item) {
      $domains[$item->target_id] = $item->target_id;
    }

    return $domains;
  }

  /**
   * Return Block content entity ID related to active domain and requested UUID.
   *
   * @param string $uuid
   *   Entity UUID.
   *
   * @return int
   *   Block content entity ID on success or 0 otherwise.
   */
  public function getBlockContentDomainChildId($uuid) {
    $ids = $this->getBlockContentDomainChildrenIds($uuid, TRUE);
    return $ids ? reset($ids) : 0;
  }

  /**
   * Return Block content entity IDs related to requested UUID.
   *
   * @param string $uuid
   *   Entity UUID.
   * @param bool $domain_related
   *   Related to the currently active domain only.
   *
   * @return array
   *   Block content entity IDs.
   */
  public function getBlockContentDomainChildrenIds($uuid, $domain_related = FALSE) {
    $query = $this->entityQuery
      ->get('block_content')
      ->condition(self::FIELD_NAME, $uuid)
      ->accessCheck(FALSE);

    if ($domain_related) {
      $query->condition(
        DomainEntityMapper::FIELD_NAME,
        $this->domainNegotiator->getActiveId()
      );
    }

    return $query->execute();
  }

  /**
   * Return Block content parent entity ID related to requested UUID.
   *
   * @param string $uuid
   *   Entity UUID.
   *
   * @return int
   *   Block content entity ID.
   */
  public function getBlockContentDomainParentId($uuid) {
    $result = $this->entityQuery
      ->get('block_content')
      ->condition('uuid', $uuid)
      ->accessCheck(FALSE)
      ->execute();

    return $result ? reset($result) : 0;
  }

  /**
   * Return all domain IDs already in use by content blocks.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $block_content
   *   Entity object.
   *
   * @return array
   *   List of domain IDs already in use on success or empty array otherwise.
   */
  public function getAllUsedDomainIds(FieldableEntityInterface $block_content) {
    $domain_ids = [];

    if (!$this->isCorrectEntity($block_content)) {
      return $domain_ids;
    }

    $blocks = [];
    $storage = $this->entityTypeManager->getStorage('block_content');
    $uuid = $block_content->get(self::FIELD_NAME)->value;

    if ($uuid) {
      $ids = $this->getBlockContentDomainChildrenIds($uuid, FALSE);

      // Remove current block content ID from the list.
      unset($ids[$block_content->id()]);

      if ($ids) {
        $blocks = $storage->loadMultiple($ids);
      }

      // Get and append parent block content entity to the blocks list.
      $parent_id = $this->getBlockContentDomainParentId($uuid);

      if ($parent_id) {
        $blocks[$parent_id] = $storage->load($parent_id);
      }
    }
    else {
      $ids = $this->getBlockContentDomainChildrenIds($block_content->uuid(), FALSE);

      if ($ids) {
        $blocks = $storage->loadMultiple($ids);
      }
    }

    foreach ($blocks as $block) {
      $block_domain_ids = $this->getEntityRelatedDomains($block);

      if ($block_domain_ids) {
        $domain_ids = array_merge($domain_ids, $block_domain_ids);
      }
    }

    return $domain_ids;
  }

  /**
   * Check is current entity correct.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   Entity object.
   *
   * @return bool
   *   Result of check.
   */
  public function isCorrectEntity(FieldableEntityInterface $entity) {

    // If domain block content parent field not available - no restrictions.
    if (!$entity->hasField(self::FIELD_NAME)) {
      return FALSE;
    }
    // If domain entity field not available - no restrictions.
    if (!$entity->hasField(DomainEntityMapper::FIELD_NAME)) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Invalidate all tags related to requested block.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $block_content
   *   Entity object.
   *
   * @return bool
   *   Result of action.
   */
  public function invalidateRelatedCaches(FieldableEntityInterface $block_content) {
    $blocks = $this->getAllBlocks($block_content);

    $tags = [];
    foreach ($blocks as $block) {
      $tags = array_merge($tags, $block->getCacheTagsToInvalidate());
    }

    if (!$tags) {
      return FALSE;
    }

    Cache::invalidateTags($tags);
    return TRUE;
  }

  /**
   * Return all blocks related to requested block (with requested block).
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $block_content
   *   Entity object.
   *
   * @return array
   *   List of all related blocks + requested block.
   */
  public function getAllBlocks(FieldableEntityInterface $block_content) {
    $blocks = [];

    if (!$this->isCorrectEntity($block_content)) {
      return $blocks;
    }

    $storage = $this->entityTypeManager->getStorage('block_content');
    $uuid = $block_content->get(self::FIELD_NAME)->value;

    if ($uuid) {
      $ids = $this->getBlockContentDomainChildrenIds($uuid, FALSE);

      if ($ids) {
        $blocks = $storage->loadMultiple($ids);
      }

      // Get and append parent block content entity to the blocks list.
      $parent_id = $this->getBlockContentDomainParentId($uuid);

      if ($parent_id) {
        $blocks[$parent_id] = $storage->load($parent_id);
      }
    }
    else {
      $ids = $this->getBlockContentDomainChildrenIds($block_content->uuid(), FALSE);

      if ($ids) {
        $blocks = $storage->loadMultiple($ids);
      }

      $blocks[$block_content->id()] = $block_content;
    }

    return $blocks;
  }

}
