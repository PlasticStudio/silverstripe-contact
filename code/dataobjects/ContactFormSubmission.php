<?php

class ContactFormSubmission extends DataObject {

    public static $db = array(
		'Name' => 'Varchar(255)',
		'Email' => 'Varchar(255)',
		'Phone' => 'Varchar(255)',
		'Message' => 'Text'
    );

    public static $has_one = array(
        'ContactPage' => 'ContactPage'
    );

    public static $summary_fields = array(
        'FormattedCreated' => 'Date',
		'Name' => 'Name',
		'Email' => 'Email',
		'Phone' => 'Phone'
    );

	function getCMSFields(){

        $fields = new FieldList(
			new TextField('Name', 'Name'),
			new TextField('Email', 'Email'),
			new TextField('Phone', 'Phone'),
			new TextareaField('Message', 'Message')
		);

        return $fields;
    }

	function FormattedCreated(){
		return date('d M Y (h:ia)', strtotime($this->Created));
		return $this->Created;
	}

}
