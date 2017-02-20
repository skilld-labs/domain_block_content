<?php

namespace Drupal\domain_block_content\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\entity_clone\Form\EntityCloneForm;

/**
 * Implements an entity Clone form.
 */
class DomainBlockContentCloneForm extends EntityCloneForm {

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\entity_clone\EntityClone\EntityCloneInterface $entity_clone_handler */
    $entity_clone_handler = $this->entityTypeManager->getHandler($this->entityTypeDefinition->id(), 'entity_clone');
    if ($this->entityTypeManager->hasHandler($this->entityTypeDefinition->id(), 'entity_clone_form')) {
      $entity_clone_form_handler = $this->entityTypeManager->getHandler($this->entityTypeDefinition->id(), 'entity_clone_form');
    }

    $properties = [];
    if (isset($entity_clone_form_handler) && $entity_clone_form_handler) {
      $properties = $entity_clone_form_handler->getNewValues($form_state);
    }

    $cloned_entity = $entity_clone_handler->cloneEntity($this->entity, $this->entity->createDuplicate(), $properties);

    drupal_set_message($this->stringTranslationManager->translate('The entity <em>@entity (@entity_id)</em> of type <em>@type</em> was cloned', [
      '@entity' => $this->entity->label(),
      '@entity_id' => $this->entity->id(),
      '@type' => $this->entity->getEntityTypeId(),
    ]));

    // Remove "destination" param from parameter bag
    // for prevent overriding form redirect url using.
    $this->getRequest()->query->remove('destination');

    // Redirect user to the newly added content block for setup it.
    $form_state->setRedirect(
      'entity.block_content.edit_form',
      ['block_content' => $cloned_entity->id()],
      ['query' => ['destination' => '/admin/structure/block/block-content']]
    );
  }

}
