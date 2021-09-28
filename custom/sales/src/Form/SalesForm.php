<?php
namespace Drupal\sales\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Drupal\file\Entity\File;
use Drupal\common_utilities\Utilities\commonUtil;

class SalesForm extends FormBase {

  public function getFormId() {
    return 'sales_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, 
    $node_id = 0) {
    $current_user = \Drupal::currentUser();
    $current_roles = $current_user->getRoles();

    $form['#title'] = 'Sales';
    $form = array(
      '#attributes' => array('enctype' => 'multipart/form-data'),
    );

      $form['sales_info'] = array(
        '#type' => 'fieldset',
        '#title' => $this
            ->t('Sales Information'),
        '#attributes' => array(
            'class' => array('SectionContainer'), 
        )            
      );

      $form['sales_info']['node_id'] = [
        '#type' => 'hidden',
        '#required' => TRUE,
        '#default_value' => $node_id,
      ];

      $form['sales_info']['sales_date'] = [
        '#type' => 'date',
        '#title' => $this->t('Sales date'),
        '#date_date_format' => 'd-m-Y',
      ];

      $form['sales_info']['source_of_lead'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Source of Lead'),
        '#required' => TRUE,        
        '#attributes' => array(
            'autocomplete' => 'nope',
        ),
      ];

      $form['sales_info']['client_phone'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Client Phone'),
        '#attributes' => array(
            'autocomplete' => 'nope',
        ),
      ];

      $form['sales_info']['client_email'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Client'),
        '#attributes' => array(
            'autocomplete' => 'nope',
        ),
      ];
      
      $form['sales_info']['project'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Project'),
        '#attributes' => array(
            'autocomplete' => 'nope',
        ),
      ];      

      $form['sales_info']['sales_person'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Sales Person'),
        '#attributes' => array(
            'autocomplete' => 'nope',
        ),
      ];

      $form['sales_info']['estimated_value'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Estimated Value (excl VAT)'),
        '#attributes' => array(
            'autocomplete' => 'nope',
        ),
      ];
      
      $form['sales_info']['key_decision_makers'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Key decision makers'),
        '#attributes' => array(
            'autocomplete' => 'nope',
        ),
      ];

      $form['sales_info']['mode'] = [
        '#type' => 'select',
        '#options' => array('Webinar', 'In-Person'), 
        '#title' => $this->t('Mode'),
      ];   

      $form['sales_info']['boq_person'] = [
        '#type' => 'textfield',
        '#title' => $this->t('BoQ Person'),
        '#attributes' => array(
            'autocomplete' => 'nope',
        ),
      ];
      
      $form['sales_info']['current_status'] = [
        '#type' => 'select',
        '#options' => array('Won', 'On Hold'), 
        '#title' => $this->t('Current Status'),
      ];  

      $form['sales_info']['reason_failed'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Reason for Failure'),
        '#attributes' => array(
            'autocomplete' => 'nope',
        ),
      ];

      $form['sales_info']['feedback'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Feedback'),
        '#attributes' => array(
            'autocomplete' => 'nope',
        ),
      ];
      
      $form['sales_info']['likely_close_date'] = [
        '#type' => 'date',
        '#title' => $this->t('Likely Close Date'),
        '#date_date_format' => 'd-m-Y',
      ];

    $form['actions'] = [
        '#type' => 'actions',
    ];
    
    $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Save'),
    ];
    

     // Adding js and css
    $form['#attached']['library'][] = 'sales/sales_lib';

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
  }
}
