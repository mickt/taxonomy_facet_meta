<?php

namespace Drupal\taxonomy_facet_meta\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\Entity\Vocabulary;

class TaxonomyFacetMetaSettingsForm extends ConfigFormBase {

    public function getFormId() {
        return 'taxonomy_facet_meta_settings_form';
    }

    protected function getEditableConfigNames() {
        return [
            'taxonomy_facet_meta.settings',
        ];
    }

    public function buildForm(array $form, FormStateInterface $form_state) {
        $config = $this->config('taxonomy_facet_meta.settings');

        $form['description'] = [
            '#markup' => $this->t('The module only works with the timing of taxonomy. The facet_pretty_paths module must be enabled, and your facets Pretty paths coder should be - "Taxonomy term name + id" of "Taxonomy term name"'),
        ];

        $form['facet_base_url'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Facet Base URL'),
            '#default_value' => $config->get('facet_base_url'),
            '#description' => $this->t('Enter the base URL for the facet pages. For example: https://mywebsite.com/catalog'),
            '#required' => TRUE,
        ];

        $form['pretty_paths_coder'] = [
            '#type' => 'radios',
            '#title' => $this->t('Select Pretty Paths Coder Type'),
            '#default_value' => $config->get('pretty_paths_coder') ?: 'taxonomy_term_name_id',
            '#options' => [
                'taxonomy_term_name_id' => $this->t('Taxonomy term name + id'),
                'taxonomy_term_name' => $this->t('Taxonomy term name'),
            ],
            '#description' => $this->t('Choose how the Pretty Paths should be coded.'),
        ];

        $vocabularies = Vocabulary::loadMultiple();
        $num_vocabularies = count($vocabularies);
        $vocab_options = ['' => $this->t('- Select a vocabulary -')];

        foreach ($vocabularies as $vocabulary) {
            $vocab_id = $vocabulary->id();
            $vocab_options[$vocab_id] = $vocabulary->label();
        }

        $form['first_vocab_select'] = [
            '#type' => 'select',
            '#title' => $this->t('First Vocabulary'),
            '#options' => $vocab_options,
            '#default_value' => $config->get('first_vocab_select'),
        ];

        $form['second_vocab_select'] = [
            '#type' => 'select',
            '#title' => $this->t('Second Vocabulary'),
            '#options' => $vocab_options,
            '#default_value' => $config->get('second_vocab_select'),
        ];

        $first_vocab_id = $config->get('first_vocab_select');
        if (!empty($first_vocab_id)) {
            $form['first_vocab_fields'] = $this->generateTemplateFields($first_vocab_id, $config);
        }

        $second_vocab_id = $config->get('second_vocab_select');
        if (!empty($second_vocab_id)) {
            $form['second_vocab_fields'] = $this->generateTemplateFields($second_vocab_id, $config);
        }

        $form['double_token_description'] = [
            '#markup' => $this->t('Fields for templates if we have more that one vocabulary'),
        ];

        if ($num_vocabularies > 1) {
            $form['double_token_title_template'] = [
                '#type' => 'textfield',
                '#title' => $this->t('Title template with two tokens'),
                '#default_value' => $config->get('double_token_title_template'),
                '#description' => $this->t('Enter title template using tokens like [token1], [token2] ... .'),
            ];

            $form['double_token_description_template'] = [
                '#type' => 'textfield',
                '#title' => $this->t('Description template with two tokens'),
                '#default_value' => $config->get('double_token_description_template'),
                '#description' => $this->t('Enter description template using tokens like [token1], [token2] ...'),
            ];

            $form['double_token_h1_template'] = [
                '#type' => 'textfield',
                '#title' => $this->t('H1 template with two tokens'),
                '#default_value' => $config->get('double_token_h1_template'),
                '#description' => $this->t('Enter H1 template using tokens like [token1], [token2] ...'),
            ];
        }

        return parent::buildForm($form, $form_state);
    }

    private function generateTemplateFields($vocab_id, $config) {
        $vocab_name = Vocabulary::load($vocab_id)->label();
        $fields = [];
    
        $fields['facet_alias_' . $vocab_id] = [
            '#type' => 'textfield',
            '#title' => $this->t('Facet URL alias for @name', ['@name' => $vocab_name]),
            '#default_value' => $config->get('facet_alias_' . $vocab_id),
            '#description' => $this->t('Enter the URL alias for the @name vocabulary.', ['@name' => $vocab_name]),
        ];
        $fields['title_template_' . $vocab_id] = [
            '#type' => 'textfield',
            '#title' => $this->t('Title template for @name', ['@name' => $vocab_name]),
            '#default_value' => $config->get('title_template_' . $vocab_id),
            '#description' => $this->t('Use [token] like an term name of token in the @name vocabulary in your title templates.', ['@name' => $vocab_name]),
        ];
        $fields['description_template_' . $vocab_id] = [
            '#type' => 'textfield',
            '#title' => $this->t('Description template for @name', ['@name' => $vocab_name]),
            '#default_value' => $config->get('description_template_' . $vocab_id),
            '#description' => $this->t('Use [token] like an term name of token in the @name vocabulary in your description templates.', ['@name' => $vocab_name]),
        ];
        $fields['h1_template_' . $vocab_id] = [
            '#type' => 'textfield',
            '#title' => $this->t('H1 template for @name', ['@name' => $vocab_name]),
            '#default_value' => $config->get('h1_template_' . $vocab_id),
            '#description' => $this->t('Use [token] like an term name of token in the @name vocabulary in your h1 templates.', ['@name' => $vocab_name]),
        ];
    
        return $fields;
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
        $config = $this->config('taxonomy_facet_meta.settings');

        $config->set('pretty_paths_coder', $form_state->getValue('pretty_paths_coder'));

        $config->set('first_vocab_select', $form_state->getValue('first_vocab_select'));
        $config->set('second_vocab_select', $form_state->getValue('second_vocab_select'));

        $this->saveVocabSettings($form_state->getValue('first_vocab_select'), $form_state, $config);
        $this->saveVocabSettings($form_state->getValue('second_vocab_select'), $form_state, $config);


        $vocabularies = Vocabulary::loadMultiple();

        if (count($vocabularies) > 1) {
            $config
                ->set('double_token_title_template', $form_state->getValue('double_token_title_template'))
                ->set('double_token_description_template', $form_state->getValue('double_token_description_template'))
                ->set('double_token_h1_template', $form_state->getValue('double_token_h1_template'));
        }

        $config->set('facet_base_url', $form_state->getValue('facet_base_url'));

        $config->save();

        parent::submitForm($form, $form_state);
    }

    private function saveVocabSettings($vocab_id, $form_state, $config) {
        if (!empty($vocab_id)) {
            $config->set('facet_alias_' . $vocab_id, $form_state->getValue('facet_alias_' . $vocab_id));
            $config->set('title_template_' . $vocab_id, $form_state->getValue('title_template_' . $vocab_id));
            $config->set('description_template_' . $vocab_id, $form_state->getValue('description_template_' . $vocab_id));
            $config->set('h1_template_' . $vocab_id, $form_state->getValue('h1_template_' . $vocab_id));
        }
    }
}
