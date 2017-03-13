<?php

namespace Drupal\domain_block_content\EntityClone\Content;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\domain_block_content\DomainBlockContentHandler;
use Drupal\domain_entity\DomainEntityMapper;
use Drupal\entity_clone\EntityClone\Content\ContentEntityCloneBase;
use Drupal\field\FieldConfigInterface;

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
        $cloned_entity->set(DomainBlockContentHandler::FIELD_NAME, $uuid);
      }
    }

    // Cleanup domain relation data.
    if ($entity->hasField(DomainEntityMapper::FIELD_NAME)) {
      $cloned_entity->set(DomainEntityMapper::FIELD_NAME, []);
    }

    // Clone referenced entities.
    if ($cloned_entity instanceof FieldableEntityInterface) {
      foreach ($cloned_entity->getFieldDefinitions() as $field_definition) {

        if ($field_definition instanceof FieldConfigInterface) {
          switch ($field_definition->getType()) {

            case 'entity_reference':
            case 'entity_reference_revisions':
              $field_name = $field_definition->getName();

              if ($field_name === DomainEntityMapper::FIELD_NAME) {
                continue;
              }
              if (!$cloned_entity->hasField($field_name)) {
                continue;
              }
              if (!method_exists($cloned_entity->{$field_name}, 'referencedEntities')) {
                continue;
              }

              $ref_clones = [];

              $referenced_entities = $cloned_entity->{$field_name}->referencedEntities();
              foreach ($referenced_entities as $referenced_entity) {
                if ($referenced_entity instanceof ContentEntityInterface) {
                  $ref_clone = $referenced_entity->createDuplicate();

                  // Cleanup domain relation data.
                  if ($ref_clone->hasField(DomainEntityMapper::FIELD_NAME)) {
                    $ref_clone->set(DomainEntityMapper::FIELD_NAME, []);
                  }
                  $ref_clone->save();

                  $ref_clones[] = [
                    'target_id' => $ref_clone->id(),
                    'target_revision_id' => $ref_clone->getRevisionId(),
                  ];
                }
              }
              $cloned_entity->get($field_name)->setValue($ref_clones);
              break;
          }
        }
      }
    }

    $cloned_entity->save();
    return $cloned_entity;
  }

}
