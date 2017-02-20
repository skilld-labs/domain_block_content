<?php

namespace Drupal\domain_block_content\EntityClone\Content;

use Drupal\Core\Entity\EntityInterface;
use Drupal\domain_entity\DomainEntityMapper;
use Drupal\entity_clone\EntityClone\Content\ContentEntityCloneBase;
use Drupal\domain_block_content\DomainBlockContentHandler;

/**
 * Class ContentEntityCloneBase.
 */
class DomainBlockContentEntityCloneBase extends ContentEntityCloneBase {

  /**
   * {@inheritdoc}
   */
  public function cloneEntity(EntityInterface $entity, EntityInterface $cloned_entity, $properties = []) {

    if ($label_key = $this->entityTypeManager->getDefinition($this->entityTypeId)->getKey('label')) {
      $cloned_entity->set($label_key, $entity->label() . ' - Cloned');
    }

    // Setup parent Block content UUID value.
    if ($entity->hasField(DomainBlockContentHandler::FIELD_NAME)) {
      $uuid = $entity->get(DomainBlockContentHandler::FIELD_NAME)->value;
      $uuid = $uuid ? $uuid : $entity->uuid();

      if ($uuid) {
        $cloned_entity->get(DomainBlockContentHandler::FIELD_NAME)->setValue($uuid);
      }
    }

    // Cleanup domain relation data.
    if ($entity->hasField(DomainEntityMapper::FIELD_NAME)) {
      $cloned_entity->get(DomainEntityMapper::FIELD_NAME)->setValue([]);
    }

    $cloned_entity->save();
    return $cloned_entity;
  }

}
