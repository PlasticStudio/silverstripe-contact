<?php

class ContactPage extends Page {

    private static $db = array(
        'ToEmail'=> 'Varchar(255)',
		'FromEmail'=> 'Varchar(255)',
		'FromName'=> 'Varchar(255)',
		'ContactDetails'=> 'HTMLText',
		'OnCompletionMessage'=> 'HTMLText'
    );

    private static $has_many = array(
        'Submissions' => 'ContactFormSubmission'
    );
	
	static $description = 'Basic contact page';
	static $icon = 'contact/images/contact-page';

    function getCMSFields(){
	
        $fields = parent::getCMSFields();
		
		// ContactDetails tab
		$fields->addFieldToTab('Root.ContactDetails', new HTMLEditorField('ContactDetails', 'Contact details'));
		
		// Emails tab
		$fields->addFieldToTab('Root.Emails', new TextareaField('ToEmail', '"To" Email<br/><em>Email addresses to deliver form submissions to. Can be comma-separated list.</em>'));
		$fields->addFieldToTab('Root.Emails', new TextField('FromEmail', '"From" & "Reply-to" Email<br/><em>Displayed in form submission email.</em>'));
		$fields->addFieldToTab('Root.Emails', new TextField('FromName', 'From name<br/><em>Displayed in form submission email. Defaults to "'.SiteConfig::current_site_config()->Title.' contact form".</em>'));
		
		// Submissions tab
        $GridFieldConfig = GridFieldConfig_RecordEditor::create();
        $SubmissionsField = new GridField(
            'Submissions',
            'Submissions',
            $this->Submissions(),
            $GridFieldConfig
        );
        $fields->addFieldToTab('Root.Submissions', $SubmissionsField);
		
		// OnCompletion tab
		$fields->addFieldToTab('Root.OnCompletion', new HTMLEditorField('OnCompletionMessage', 'Message to show after form submission'));
		
        return $fields;
		
    }



}


class ContactPage_Controller extends Page_Controller { 

    private static $allowed_actions = array(
		'ContactForm',
		'submitted'
	);

    public function init(){
        parent::init();
    }
	
	function Form(){
		
		$params = $this->request->params();
		
		if($params['Action'] == 'submitted'){
		
			return $this->OnCompletionMessage;
			
		}else{
		
			return $this->ContactForm();
			
		}
		
	}
	
	function ContactForm(){
	
		$fields = new FieldList(
			new TextField('Name', 'Name'),
			new EmailField('Email', 'Email'),
			new TextField('Phone', 'Phone'),
			new TextareaField('Message', 'Message'),
			new HiddenField('ContactPageID', null, $this->ID)
		);
		
		$actions = new FieldList(
			new FormAction('doContactForm', 'Submit')
		);
		
		// Validate required fields
		$validator = new RequiredFields('Name', 'Email','Phone', 'Message');
		
		$form = new Form($this, 'ContactForm', $fields, $actions, $validator);
		
		return $form;
	
	}
	
    function doContactForm($data, $form) {
		
		// create new ContactFormSubmission object
        $submission = new ContactFormSubmission();
        $form->saveInto($submission);
        $submission->write();
		
		// send notification email to admin
		$this->EmailAdmin($submission);
		
        $this->redirect($this->Link().'submitted');
    }
	
	/***
	* Send email
	***/
	function EmailAdmin($submission){
	
		if( !empty($this->FromName) ){
			$FromName = $this->FromName;
		}
		else{
			$FromName = $this->SiteConfig()->Title.' contact form';
		}

		$from = $FromName . ' <' . $this->FromEmail . '>';
		$to = $this->ToEmail;
		//$to = Email::setAdminEmail();
		$subject = 'A new submission has been received from '.$FromName;
		$body = '';
		
		$email = new Email($from, $to, $subject, $body);
		
		//set template
    	$email->setTemplate('AdminEmail');
		
    	//populate template
    	$email->populateTemplate($submission);
		
    	//send mail
    	$email->send();
		
	}
	
}