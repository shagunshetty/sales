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
    'project',
    'sales_person',
    'currency',
    'estimated_value',
    'inr_usd',
    'key_decision_maker',
    'engineer',
    'boq_person',
    'deal_stage',
    'result_status',
    'deal_status_updates',
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

        $arr_client_name = $node->get('field_client_name')->getValue();
        $arr_client_designation = $node->get('field_client_designation')->getValue();
        $arr_client_phone = $node->get('field_client_phone')->getValue();
        $arr_client_email = $node->get('field_client_email')->getValue();

        for($i=1; $i<=4; $i++) {
          $form_data['client_name_' . $i] = $arr_client_name[($i-1)]['value'];
          $form_data['client_designation_' . $i] = $arr_client_designation[($i-1)]['value'];
          $form_data['client_phone_' . $i] = $arr_client_phone[($i-1)]['value'];
          $form_data['client_email_' . $i] = $arr_client_email[($i-1)]['value'];
        }

        //echo "<pre>";print_r($form_data);exit;
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

      for($i=1; $i<=4; $i++) {
        $form_data['client_name_' . $i] = '';
        $form_data['client_designation_' . $i] = '';
        $form_data['client_phone_' . $i] = '';
        $form_data['client_email_' . $i] = '';
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
      '#title' => $this->t('Date of Enquiry'),
      '#date_date_format' => 'd-m-Y',
      '#default_value' => $form_data['date'],
    ];

    $arr_location = array(
      'mumbai' => 'Mumbai',
      'new_delhi' => 'New Delhi',
      'bangalore' => 'Bangalore',
      'hyderabad' => 'Hyderabad',
      'ahmedabad' => 'Ahmedabad',
      'chennai' => 'Chennai',
      'kolkata' => 'Kolkata',
      'surat' => 'Surat',
      'pune' => 'Pune',
      'jaipur' => 'Jaipur',
      'lucknow' => 'Lucknow',
      'kanpur' => 'Kanpur',
      'nagpur' => 'Nagpur',
      'indore' => 'Indore',
      'thane' => 'Thane',
      'bhopal' => 'Bhopal',
      'visakhapatnam' => 'Visakhapatnam',
      'pimpri_chinchwad' => 'Pimpri & Chinchwad',
      'patna' => 'Patna',
      'vadodara' => 'Vadodara',
      'ghaziabad' => 'Ghaziabad',
      'ludhiana' => 'Ludhiana',
      'agra' => 'Agra',
      'nashik' => 'Nashik',
      'faridabad' => 'Faridabad',
      'meerut' => 'Meerut',
      'rajkot' => 'Rajkot',
      'kalyan_dombivali' => 'Kalyan & Dombivali',
      'vasai_virar' => 'Vasai Virar',
      'varanasi' => 'Varanasi',
      'srinagar' => 'Srinagar',
      'aurangabad' => 'Aurangabad',
      'dhanbad' => 'Dhanbad',
      'amritsar' => 'Amritsar',
      'navi_mumbai' => 'Navi Mumbai',
      'allahabad' => 'Allahabad',
      'ranchi' => 'Ranchi',
      'haora' => 'Haora',
      'coimbatore' => 'Coimbatore',
      'jabalpur' => 'Jabalpur',
      'gwalior' => 'Gwalior',
      'vijayawada' => 'Vijayawada',
      'jodhpur' => 'Jodhpur',
      'madurai' => 'Madurai',
      'raipur' => 'Raipur',
      'kota' => 'Kota',
      'guwahati' => 'Guwahati',
      'chandigarh' => 'Chandigarh',
      'solapur' => 'Solapur',
      'hubli_and_dharwad' => 'Hubli and Dharwad',
      'bareilly' => 'Bareilly',
      'moradabad' => 'Moradabad',
      'gurgaon' => 'Gurgaon',
      'aligarh' => 'Aligarh',
      'jalandhar' => 'Jalandhar',
      'tiruchirappalli' => 'Tiruchirappalli',
      'bhubaneswar' => 'Bhubaneswar',
      'salem' => 'Salem',
      'mira_and_bhayander' => 'Mira and Bhayander',
      'thiruvananthapuram' => 'Thiruvananthapuram',
      'bhiwandi' => 'Bhiwandi',
      'saharanpur' => 'Saharanpur',
      'gorakhpur' => 'Gorakhpur',
      'guntur' => 'Guntur',
      'bikaner' => 'Bikaner',
      'amravati' => 'Amravati',
      'noida' => 'Noida',
      'jamshedpur' => 'Jamshedpur',
      'bhilai_nagar' => 'Bhilai Nagar',
      'warangal' => 'Warangal',
      'cuttack' => 'Cuttack',
      'firozabad' => 'Firozabad',
      'kochi' => 'Kochi',
      'bhavnagar' => 'Bhavnagar',
      'dehradun' => 'Dehradun',
      'durgapur' => 'Durgapur',
      'asansol' => 'Asansol',
      'nanded_waghala' => 'Nanded Waghala',
      'kolapur' => 'Kolapur',
      'ajmer' => 'Ajmer',
      'gulbarga' => 'Gulbarga',
      'jamnagar' => 'Jamnagar',
      'ujjain' => 'Ujjain',
      'loni' => 'Loni',
      'siliguri' => 'Siliguri',
      'jhansi' => 'Jhansi',
      'ulhasnagar' => 'Ulhasnagar',
      'nellore' => 'Nellore',
      'jammu' => 'Jammu',
      'sangli_miraj_kupwad' => 'Sangli Miraj Kupwad',
      'belgaum' => 'Belgaum',
      'mangalore' => 'Mangalore',
      'ambattur' => 'Ambattur',
      'tirunelveli' => 'Tirunelveli',
      'malegoan' => 'Malegoan',
      'gaya' => 'Gaya',
      'jalgaon' => 'Jalgaon',
      'udaipur' => 'Udaipur',
      'maheshtala' => 'Maheshtala',
      'others' => 'Others'
  );

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
    'amc' => 'AMC',
    'others' => 'Others',
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

    $form['sales_info']['client_info'] = array(
      '#type' => 'fieldset',
      '#title' => $this
          ->t('Client Info'),
      '#attributes' => array(
          'class' => array('SectionContainer'), 
      )            
    );

    for ($i=1; $i<=4; $i++) {
      $form['sales_info']['client_info']['client_'.$i] = array(
        '#type' => 'fieldset',
        '#title' => $this
            ->t('Client ('.($i+1).')'),
        '#attributes' => array(
            'class' => array('SectionContainer'), 
        )            
      );

      $form['sales_info']['client_info']['client_'.$i]['client_name_'.$i] = [
        '#type' => 'textfield',
        '#title' => $this->t('Client Name'),
        '#attributes' => array(
            'autocomplete' => 'nope',
        ),
        '#default_value' => $form_data['client_name_'.$i],
      ];

      $form['sales_info']['client_info']['client_'.$i]['client_designation_'.$i] = [
        '#type' => 'textfield',
        '#title' => $this->t('Client Designation'),
        '#attributes' => array(
            'autocomplete' => 'nope',
        ),
        '#default_value' => $form_data['client_designation_'.$i],
      ];

      $form['sales_info']['client_info']['client_'.$i]['client_phone_'.$i] = [
        '#type' => 'textfield',
        '#title' => $this->t('Client Phone'),
        '#attributes' => array(
            'autocomplete' => 'nope',
        ),
        '#default_value' => $form_data['client_phone_'.$i],
      ];

      $form['sales_info']['client_info']['client_'.$i]['client_email_'.$i] = [
        '#type' => 'textfield',
        '#title' => $this->t('Client Email'),
        '#attributes' => array(
            'autocomplete' => 'nope',
        ),
        '#default_value' => $form_data['client_email_'.$i],
      ];
    }
    
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
    
    $form['sales_info']['inr_usd'] = [
      '#type' => 'textfield',
      '#title' => $this->t('INR+USD	'),
      '#attributes' => array(
          'autocomplete' => 'nope',
      ),
      '#default_value' => $form_data['inr_usd'],
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

    $form['sales_info']['deal_status_updates'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Deal Status Updates'),
      '#attributes' => array(
          'autocomplete' => 'nope',
      ),
      '#default_value' => $form_data['deal_status_updates'],
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
     // echo "<pre>";
//print_r($form_data);exit;
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

    $arr_client_name = array();
    $arr_client_designation = array();
    $arr_client_phone = array();
    $arr_client_email = array();

    for ($i=1; $i<=4; $i++) {
      array_push($arr_client_name, $form_state->getValue('client_name_'.$i));
      array_push($arr_client_designation, $form_state->getValue('client_designation_'.$i));
      array_push($arr_client_phone, $form_state->getValue('client_phone_'.$i));
      array_push($arr_client_email, $form_state->getValue('client_email_'.$i));
    }
    $form_data['field_client_name'] = $arr_client_name;
    $form_data['field_client_designation'] = $arr_client_designation;
    $form_data['field_client_phone'] = $arr_client_phone;
    $form_data['field_client_email'] = $arr_client_email;

    $form_data['type'] = 'sales';
    return $form_data;
  }
}
