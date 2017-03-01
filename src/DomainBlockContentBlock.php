<?php

namespace Drupal\domain_block_content;

use Drupal\block_content\BlockContentInterface;
use Drupal\block_content\Plugin\Block\BlockContentBlock;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a generic custom block type.
 *
 * @Block(
 *  id = "domain_block_content",
 *  admin_label = @Translation("Custom block"),
 *  category = @Translation("Custom"),
 *  deriver = "Drupal\block_content\Plugin\Derivative\BlockContent"
 * )
 */
class DomainBlockContentBlock extends BlockContentBlock {

  /**
   * The Domain Block content handler.
   *
   * @var \Drupal\domain_block_content\DomainBlockContentHandler
   */
  protected $domainBlockContentHandler;

  /**
   * Constructs a new BlockContentBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Block\BlockManagerInterface $block_manager
   *   The Plugin Block Manager.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager service.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account for which view access should be checked.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The URL generator.
   * @param \Drupal\domain_block_content\DomainBlockContentHandler $domain_block_content_handler
   *   The Domain Block content handler.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, BlockManagerInterface $block_manager, EntityManagerInterface $entity_manager, AccountInterface $account, UrlGeneratorInterface $url_generator, DomainBlockContentHandler $domain_block_content_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $block_manager, $entity_manager, $account, $url_generator);
    $this->domainBlockContentHandler = $domain_block_content_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.block'),
      $container->get('entity.manager'),
      $container->get('current_user'),
      $container->get('url_generator'),
      $container->get('domain_block_content.handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntity() {
    if (!isset($this->blockContent)) {
      $uuid = $this->getDerivativeId();
      $block_content = $this->entityManager->loadEntityByUuid('block_content', $uuid);

      if ($block_content instanceof FieldableEntityInterface) {

        if ($this->domainBlockContentHandler->isAccessibleForCurrentDomain($block_content, $uuid)) {
          $this->blockContent = $block_content;
        }
        elseif ($this->domainBlockContentHandler->getField('block_content', $block_content->bundle())) {
          $id = $this->domainBlockContentHandler->getBlockContentDomainChildId($uuid);
          $this->blockContent = $this->entityManager->getStorage('block_content')->load($id);

          // Replace current block title with title
          // from loaded block for having actual version.
          if ($this->blockContent instanceof BlockContentInterface) {
            $this->setConfigurationValue('label', $this->blockContent->label());
          }
        }
      }
    }
    return $this->blockContent;
  }

}
