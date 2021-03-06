<?php

namespace Drupal\imagefield_tokens\Plugin\Field\FieldWidget;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Image\ImageFactory;
use Drupal\Core\Render\ElementInfoManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\file\Entity\File;
use Drupal\image\Plugin\Field\FieldWidget\ImageWidget;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'image_image' widget.
 *
 * @FieldWidget(
 *   id = "imagefield_tokens",
 *   label = @Translation("Image Field Tokens"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */
class ImageFieldTokensWigdet extends ImageWidget {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Constructs a new ImageFieldTokensWigdet object.
   *
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   Field definition.
   * @param array $settings
   *   Field settings.
   * @param array $third_party_settings
   *   Third party settings.
   * @param \Drupal\Core\Render\ElementInfoManagerInterface $element_info
   *   The element info manager.
   * @param \Drupal\Core\Image\ImageFactory $image_factory
   *   The image factory.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Current user service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, ElementInfoManagerInterface $element_info, ImageFactory $image_factory, AccountInterface $current_user, ModuleHandlerInterface $module_handler, EntityRepositoryInterface $entity_repository) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings, $element_info, $image_factory);
    $this->currentUser = $current_user;
    $this->moduleHandler = $module_handler;
    $this->entityRepository = $entity_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('element_info'),
      $container->get('image.factory'),
      $container->get('current_user'),
      $container->get('module_handler'),
      $container->get('entity.repository')
    );

  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    // Get setting for token.
    $field_settings = $this->getFieldSettings();
    $entity_type_id = $form_state->getFormObject()->getEntity()->getEntityTypeId();

    // Add image validation.
    $element['#upload_validators']['file_validate_is_image'] = [];

    // Add upload resolution validation.
    if ($field_settings['max_resolution'] || $field_settings['min_resolution']) {
      $element['#upload_validators']['file_validate_image_resolution'] = [$field_settings['max_resolution'], $field_settings['min_resolution']];
    }

    $extensions = $field_settings['file_extensions'];
    $supported_extensions = $this->imageFactory->getSupportedExtensions();

    // If using custom extension validation, ensure that the extensions are
    // supported by the current image toolkit. Otherwise, validate against all
    // toolkit supported extensions.
    $extensions = !empty($extensions) ? array_intersect(explode(' ', $extensions), $supported_extensions) : $supported_extensions;
    $element['#upload_validators']['file_validate_extensions'][0] = implode(' ', $extensions);

    // Add mobile device image capture acceptance.
    $element['#accept'] = 'image/*';

    // Add properties needed by process() method.
    $element['#preview_image_style'] = $this->getSetting('preview_image_style');
    $element['#title_field'] = $field_settings['title_field'];
    $element['#title_field_required'] = $field_settings['title_field_required'];
    $element['#alt_field'] = $field_settings['alt_field'];
    $element['#alt_field_required'] = $field_settings['alt_field_required'];

    // Default image.
    $default_image = $field_settings['default_image'];
    if (empty($default_image['uuid'])) {
      $default_image = $this->fieldDefinition->getFieldStorageDefinition()->getSetting('default_image');
    }
    // Convert the stored UUID into a file ID.
    if (!empty($default_image['uuid']) && $entity = $this->entityRepository->loadEntityByUuid('file', $default_image['uuid'])) {
      $default_image['fid'] = $entity->id();
    }
    $element['#default_image'] = !empty($default_image['fid']) ? $default_image : [];
    if (!$this->currentUser->isAnonymous()) {
      // Add token link to the form.
      $form['#token'] = TRUE;
      if ($this->moduleHandler->moduleExists('token')) {
        $form['token_tree'] = [
          '#theme' => 'token_tree_link',
          '#token_types' => [$entity_type_id],
          '#show_restricted' => TRUE,
          '#weight' => 90,
        ];
      }
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function process($element, FormStateInterface $form_state, $form) {
    $entity_type = '';
    $current_entity = NULL;
    $field_settings = [];
    // Get form object to retrieve parent entity.
    $form_object = $form_state->getFormObject();
    $get_entity = method_exists($form_object, 'getEntity');

    if ($get_entity) {
      $current_entity = $form_object->getEntity();
      if (!empty($current_entity)) {
        // Get entity data.
        $entity_type = $current_entity->getEntityTypeId();
        $entity_bundle = $current_entity->bundle();
        // Get field settings.
        $field_name = $element['#field_name'];
        $field_config = FieldConfig::loadByName($entity_type, $entity_bundle, $field_name);
        $field_settings = $field_config->getSettings();
      }
    }

    $item = $element['#value'];
    $item['fids'] = $element['fids']['#value'];
    $alt_token = '';
    $title_token = '';
    // Fill alt & title fields from default image settings if they are empty.
    $condition = !empty($field_settings) && !empty($field_settings['default_image']) && (empty($item['alt']) || empty($item['title'])) && (!empty($element['#default_value']['display']) || empty($element['#default_value']['alt']));
    if ($condition) {
      $item['alt'] = $field_settings['default_image']['alt'];
      $item['title'] = $field_settings['default_image']['title'];
    }

    $element['#theme'] = 'image_widget';

    // Add the image preview.
    if (!empty($element['#files']) && $element['#preview_image_style']) {
      $file = reset($element['#files']);
      $variables = [
        'style_name' => $element['#preview_image_style'],
        'uri' => $file->getFileUri(),
      ];

      // Determine image dimensions.
      if (isset($element['#value']['width']) && isset($element['#value']['height'])) {
        $variables['width'] = $element['#value']['width'];
        $variables['height'] = $element['#value']['height'];
      }
      else {
        $image = \Drupal::service('image.factory')->get($file->getFileUri());
        if ($image->isValid()) {
          $variables['width'] = $image->getWidth();
          $variables['height'] = $image->getHeight();
        }
        else {
          $variables['width'] = $variables['height'] = NULL;
        }
      }

      $element['preview'] = [
        '#weight' => -10,
        '#theme' => 'image_style',
        '#width' => $variables['width'],
        '#height' => $variables['height'],
        '#style_name' => $variables['style_name'],
        '#uri' => $variables['uri'],
      ];

      // Store the dimensions in the form so the file doesn't have to be
      // accessed again. This is important for remote files.
      $element['width'] = [
        '#type' => 'hidden',
        '#value' => $variables['width'],
      ];
      $element['height'] = [
        '#type' => 'hidden',
        '#value' => $variables['height'],
      ];
    }
    elseif (!empty($element['#default_image'])) {
      $default_image = $element['#default_image'];
      $file = File::load($default_image['fid']);
      if (!empty($file)) {
        $element['preview'] = [
          '#weight' => -10,
          '#theme' => 'image_style',
          '#width' => $default_image['width'],
          '#height' => $default_image['height'],
          '#style_name' => $element['#preview_image_style'],
          '#uri' => $file->getFileUri(),
        ];
      }
    }

    if (isset($item['alt'])) {
      $alt_token = \Drupal::token()->replace($item['alt'], [$entity_type => $current_entity]);
      if (empty($alt_token)) {
        $alt_token = $item['alt'];
      }
    }
    if (isset($item['title'])) {
      $title_token = \Drupal::token()->replace($item['title'], [$entity_type => $current_entity]);
      if (empty($title_token)) {
        $title_token = $item['title'];
      }
    }

    // Add the additional alt and title fields.
    $element['alt'] = [
      '#title' => new TranslatableMarkup('Alternative text'),
      '#type' => 'textfield',
      '#default_value' => $alt_token ?? '',
      '#description' => new TranslatableMarkup('This text will be used by screen readers, search engines, or when the image cannot be loaded.'),
      // @see https://www.drupal.org/node/465106#alt-text
      '#maxlength' => 512,
      '#weight' => -12,
      '#access' => (bool) $item['fids'] && $element['#alt_field'],
      '#required' => $element['#alt_field_required'],
      '#element_validate' => $element['#alt_field_required'] === 1 ? [[get_called_class(), 'validateRequiredFields']] : [],
    ];

    $element['title'] = [
      '#type' => 'textfield',
      '#title' => new TranslatableMarkup('Title'),
      '#default_value' => $title_token ?? '',
      '#description' => new TranslatableMarkup('The title is used as a tool tip when the user hovers the mouse over the image.'),
      '#maxlength' => 1024,
      '#weight' => -11,
      '#access' => (bool) $item['fids'] && $element['#title_field'],
      '#required' => $element['#title_field_required'],
      '#element_validate' => $element['#title_field_required'] === 1 ? [[get_called_class(), 'validateRequiredFields']] : [],
    ];

    $element['#value']['alt'] = $alt_token;
    $element['#value']['title'] = $title_token;

    return $element;
  }

}
