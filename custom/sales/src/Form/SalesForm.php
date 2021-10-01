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

  var $form_field = array(
    'date',
    'location',
    'deal_hotness',
    'company_name',
    'project_class',
    'project_type',
    'source_of_lead',
    'client_name',
    'client_designation',
    'project',
    'sales_person',
    'currency',
    'estimated_value',
    'key_decision_maker',
    'engineer',
    'boq_person',
    'deal_stage',
    'result_status',
    'feedback',
    'likely_close_date'
  );

  public function getFormId() {
    return 'sales_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, 
    $node_id = 0) {

    $flag_enable_edit = false;
    $current_user = \Drupal::currentUser();
    $current_roles = $current_user->getRoles();
    $form_field = $this->form_field;
    
    if ($node_id > 0) {
      // Edit Mode
      $node = Node::load($node_id);
      if ($node != NULL && $node->isPublished()) {
        $form['#title'] = 'Modify Sales';
        $flag_enable_edit = true;

        $form_data = array();
        foreach ($form_field as $field_name) {
          $form_data[$field_name] = $node->get('field_'.$field_name)->getString();
        }

        $arr_client_phone = $node->get('field_client_phone')->getValue();
        for($i=0; $i<=3; $i++) {
          $form_data['client_phone_' . ($i+1)] = $arr_client_phone[$i]['value'];
        }

        $arr_client_email = $node->get('field_client_email')->getValue();
        for($i=0; $i<=3; $i++) {
          $form_data['client_email_' . ($i+1)] = $arr_client_email[$i]['value'];
        }

//        print_r($form_data);exit;
        // get created timestamp
        $created_timestamp = 'Created on ' . date("d F Y H:i", $node->getCreatedTime());
      } else {
          commonUtil::my_goto_error();
          return;
      }
    } else {
      $form['#title'] = 'Sales';

      // New Mode
      $form_data = array();
      foreach ($form_field as $field_name) {
        $form_data[$field_name] = '';
      }

      for($i=0; $i<=3; $i++) {
        $form_data['client_phone_' . ($i+1)] = '';
      }

      for($i=0; $i<=3; $i++) {
        $form_data['client_email_' . ($i+1)] = '';
      }
    }

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

    $form['sales_info']['date'] = [
      '#type' => 'date',
      '#title' => $this->t('Date'),
      '#date_date_format' => 'd-m-Y',
      '#default_value' => $form_data['date'],
    ];

    $arr_location = array(
    'mumbai' => 'Mumbai',
    'new_delhi' => 'New Delhi',
    'bangalore' => 'Bangalore',
    'hyderabad' => 'Hyderabad');

    $form['sales_info']['location'] = [
      '#type' => 'select',
      '#options' => $arr_location, 
      '#title' => $this->t('Location'),
      '#default_value' => $form_data['location'],
    ];  

    $arr_deal_hotness = array(
    'hot' => 'Hot',
    'warm' => 'Warm',
    'cold' => 'Cold'
    );

    $form['sales_info']['deal_hotness'] = [
      '#type' => 'select',
      '#options' => $arr_deal_hotness, 
      '#title' => $this->t('Deal Hotness'),
      '#default_value' => $form_data['deal_hotness'],
    ];

    $form['sales_info']['company_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Company Name'),
      '#required' => TRUE,        
      '#attributes' => array(
          'autocomplete' => 'nope',
      ),
      '#default_value' => $form_data['company_name'],
    ];

    $arr_project_class = array(
    'a_class' => 'A Class (Services / Software / AMC)',
    'b_class' => 'B Class( Design Build)',
    'c_class' =>'C Class (Build Bid with alternates allowed)',
    'd_class' => 'D Class (Strict RFP)'
    );

    $form['sales_info']['project_class'] = [
      '#type' => 'select',
      '#options' => $arr_project_class, 
      '#title' => $this->t('Project Class'),
      '#default_value' => $form_data['project_class'],
    ];

    $arr_project_type = array(
    'auditorium' => 'Auditorium',
    'corporate_facility' => 'Corporate Facility',
    'command_center' => 'Command Center',
    'training_facility' => 'Training Facility',
    'event_space' => 'Event Space',
    'single_room' => 'Single Room',
    'multiple' => 'Multiple',
    'residential' => 'Residential',
    'professional_services' => 'Professional Services',
    'amc' => 'AMC'
    );

    $form['sales_info']['project_type'] = [
      '#type' => 'select',
      '#options' => $arr_project_type, 
      '#title' => $this->t('Project Type'),
      '#default_value' => $form_data['project_type'],
    ];
    
    $form['sales_info']['source_of_lead'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Source of Lead'),
      '#required' => TRUE,        
      '#attributes' => array(
          'autocomplete' => 'nope',
      ),
      '#default_value' => $form_data['source_of_lead'],
    ];
    
    $form['sales_info']['client_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client Name'),
      '#attributes' => array(
          'autocomplete' => 'nope',
      ),
      '#default_value' => $form_data['client_name'],
    ];

    $form['sales_info']['client_designation'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client Designation'),
      '#attributes' => array(
          'autocomplete' => 'nope',
      ),
      '#default_value' => $form_data['client_designation'],
    ];

    $form['sales_info']['client_phone_fieldset'] = array(
      '#type' => 'fieldset',
      '#title' => $this
          ->t('Client Phone'),
      '#attributes' => array(
          'class' => array('SectionContainer'), 
      )            
    );

    $form['sales_info']['client_phone_fieldset']['client_phone_1'] = [
      '#type' => 'textfield',
      '#attributes' => array(
          'autocomplete' => 'nope',
      ),
      '#default_value' => $form_data['client_phone_1'],
    ];

    $form['sales_info']['client_phone_fieldset']['client_phone_2'] = [
      '#type' => 'textfield',
      '#attributes' => array(
          'autocomplete' => 'nope',
      ),
      '#default_value' => $form_data['client_phone_2'],
    ];

    $form['sales_info']['client_phone_fieldset']['client_phone_3'] = [
      '#type' => 'textfield',
      '#attributes' => array(
          'autocomplete' => 'nope',
      ),
      '#default_value' => $form_data['client_phone_3'],
    ];

    $form['sales_info']['client_phone_fieldset']['client_phone_4'] = [
      '#type' => 'textfield',
      '#attributes' => array(
          'autocomplete' => 'nope',
      ),
      '#default_value' => $form_data['client_phone_4'],
    ];

    $form['sales_info']['client_email_fieldset'] = array(
      '#type' => 'fieldset',
      '#title' => $this
          ->t('Client Email'),
      '#attributes' => array(
          'class' => array('SectionContainer'), 
      )            
    );

    $form['sales_info']['client_email_fieldset']['client_email_1'] = [
      '#type' => 'textfield',
      '#attributes' => array(
          'autocomplete' => 'nope',
      ),
      '#default_value' => $form_data['client_email_1'],
    ];

    $form['sales_info']['client_email_fieldset']['client_email_2'] = [
      '#type' => 'textfield',
      '#attributes' => array(
          'autocomplete' => 'nope',
      ),
      '#default_value' => $form_data['client_email_2'],
    ];

    $form['sales_info']['client_email_fieldset']['client_email_3'] = [
      '#type' => 'textfield',
      '#attributes' => array(
          'autocomplete' => 'nope',
      ),
      '#default_value' => $form_data['client_email_3'],
    ];

    $form['sales_info']['client_email_fieldset']['client_email_4'] = [
      '#type' => 'textfield',
      '#attributes' => array(
          'autocomplete' => 'nope',
      ),
      '#default_value' => $form_data['client_email_4'],
    ];
    
    $form['sales_info']['project'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Project'),
      '#attributes' => array(
          'autocomplete' => 'nope',
      ),
      '#default_value' => $form_data['project'],
    ];      

    $form['sales_info']['sales_person'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Sales Person'),
      '#attributes' => array(
          'autocomplete' => 'nope',
      ),
      '#default_value' => $form_data['sales_person'],
    ];

    $arr_currency = array(
    'inr' => 'INR',
    'usd' => 'USD'
    );

    $form['sales_info']['currency'] = [
      '#type' => 'select',
      '#options' => $arr_currency, 
      '#title' => $this->t('Currency'),
      '#default_value' => $form_data['currency'],
    ];

    $form['sales_info']['estimated_value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Estimated Value (excl VAT)'),
      '#attributes' => array(
          'autocomplete' => 'nope',
      ),
      '#default_value' => $form_data['estimated_value'],
    ];
    
    $form['sales_info']['key_decision_maker'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Key decision makers'),
      '#attributes' => array(
          'autocomplete' => 'nope',
      ),
      '#default_value' => $form_data['key_decision_maker'],
    ];

    $form['sales_info']['engineer'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Design & Estimation Engineer'),
      '#attributes' => array(
          'autocomplete' => 'nope',
      ),
      '#default_value' => $form_data['engineer'],
    ];

    $form['sales_info']['boq_person'] = [
      '#type' => 'textarea',
      '#title' => $this->t('BoQ Person'),
      '#attributes' => array(
          'autocomplete' => 'nope',
      ),
      '#default_value' => $form_data['boq_person'],
    ];
    
    $arr_deal_stage = array(
    'suspect' => 'Suspect',
    'prospect' => 'Prospect',
    'qualified_lead' => 'Qualified Lead',
    'preliminary_design' => 'Preliminary Design',
    'proposal_sent' => 'Proposal Sent',
    'discussion_or_proposals' => 'Discussion or proposals',
    'negotiation' => 'Negotiation',
    'results_received' => 'Results Received'
    );

    $form['sales_info']['deal_stage'] = [
      '#type' => 'select',
      '#options' => $arr_deal_stage, 
      '#title' => $this->t('Deal Stage'),
      '#default_value' => $form_data['deal_stage'],
    ];

    $arr_result_status = array(
      'won' => 'Won',
      'lost' => 'Lost',
      'dropped' => 'Dropped',
      'hold' => 'Hold',
    );

    $form['sales_info']['result_status'] = [
      '#type' => 'select',
      '#options' => $arr_result_status, 
      '#title' => $this->t('Result Status'),
      '#default_value' => $form_data['result_status'],
    ];

    $form['sales_info']['feedback'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Feedback'),
      '#attributes' => array(
          'autocomplete' => 'nope',
      ),
      '#default_value' => $form_data['feedback'],
    ];
    
    $form['sales_info']['likely_close_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Likely Close Date'),
      '#date_date_format' => 'd-m-Y',
      '#default_value' => $form_data['likely_close_date'],
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
    if ($form_state->getValue('node_id') > 0) {
      // Update Record
      $node_id = $form_state->getValue('node_id');
      $node = Node::load($node_id);
      if ($node == null) {
        commonUtil::my_goto_error();
        return;
      }

      $form_data = $this->_getFormData($form_state);

      foreach ($form_data as $field_name => $field_value) {
        $node->set($field_name, $field_value);
      }

      $node->save();
      \Drupal::messenger()->addStatus("Record has been updated!\n");

    } else {
      // New Record
      $form_data = $this->_getFormData($form_state);
      $form_data['title'] = 'Sales - ' . time();
  
      //print_r($form_data);exit;
      $node = Node::create($form_data);
  
      $node->save();
      $node_id = preg_replace("/[^0-9]/", "", $node->id());
      \Drupal::messenger()->addStatus("Record has been created!\n");
          commonUtil::my_goto(Url::fromRoute('sales.manage',
                              array('node_id' => $node_id))->toString());
    }
  }

  private function _getFormData(FormStateInterface $form_state) {
    $form_field = $this->form_field;
    foreach ($form_field as $field_name) {
      $form_data['field_'.$field_name] = $form_state->getValue($field_name);
    }

    $arr_client_phone = array();
    $arr_client_email = array();
    array_push($arr_client_phone
      , $form_state->getValue('client_phone_1')
      , $form_state->getValue('client_phone_2')
      , $form_state->getValue('client_phone_3')
      , $form_state->getValue('client_phone_4'));

    array_push($arr_client_email
      , $form_state->getValue('client_email_1')
      , $form_state->getValue('client_email_2')
      , $form_state->getValue('client_email_3')
      , $form_state->getValue('client_email_4'));

    $form_data['field_client_phone'] = $arr_client_phone;
    $form_data['field_client_email'] = $arr_client_email;

    $form_data['type'] = 'sales';
    return $form_data;
  }
}
