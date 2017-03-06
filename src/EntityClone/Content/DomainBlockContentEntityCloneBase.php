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
      $languages = $cloned_entity->getTranslationLanguages();

      foreach ($languages as $language) {
        $cloned_entity_translation = $cloned_entity->getTranslation($language->getId());

        if ($cloned_entity_translation) {
          $cloned_entity_translation->set($label_key, $cloned_entity_translation->label() . ' - Cloned');
          $cloned_entity_translation->save();
        }
      }
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
