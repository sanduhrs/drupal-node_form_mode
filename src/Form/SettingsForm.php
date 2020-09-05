<?php

namespace Drupal\node_form_mode\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Node form mode settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'node_form_mode_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['node_form_mode.settings'];
  }

  /**
   * Get the form mode options.
   *
   * @param string $bundle
   *   The entity type bundle.
   *
   * @return array
   *   An options array.
   */
  private function getOptions($bundle) {
    /** @var \Drupal\Core\Entity\EntityDisplayRepository $entity_display_repository */
    $entity_display_repository = \Drupal::service('entity_display.repository');
    $form_modes = $entity_display_repository->getFormModeOptionsByBundle('node', $bundle);
    $options = [];
    foreach ($form_modes as $form_mode_id => $form_mode) {
      $options[$form_mode_id] = $form_mode;
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $node_types = \Drupal::entityTypeManager()
      ->getStorage('node_type')
      ->loadMultiple();
    foreach ($node_types as $node_type) {
      $form['form_mode_' . $node_type->id()] = [
        '#type' => 'select',
        '#title' => $this->t('@bundle form mode', ['@bundle' => $node_type->label()]),
        '#options' => $this->getOptions($node_type->id()),
        '#default_value' => $this->config('node_form_mode.settings')->get('form_mode_' . $node_type->id()),
      ];
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $node_types = \Drupal::entityTypeManager()
      ->getStorage('node_type')
      ->loadMultiple();
    foreach ($node_types as $node_type) {
      $this->config('node_form_mode.settings')
        ->set('form_mode_' . $node_type->id(), $form_state->getValue('form_mode_' . $node_type->id()))
        ->save();
    }
    parent::submitForm($form, $form_state);
  }

}
