<?php

class Manager_EmailMessage {

    public static function createByEmailDefinitionWithRecipients($context, $searches, $replaces, $recipients){
        $emailDefinitions = self::getEmailDefinitions($context);
        $emailBody = self::prepareEmailBody($emailDefinitions, $searches, $replaces);

        return self::createQuicklyWithRecipients($context, $emailDefinitions['subject'], $emailBody, $recipients);
    }

    public static function getEmailDefinitions($context){
        $definitions = Zend_Registry::get('email_definitions');

        $contextDefinitions = $definitions->$context;

        $layout = isset($contextDefinitions->layout) ? $contextDefinitions->layout : $definitions->default->layout;

        $subject = isset($contextDefinitions->subject) ? $contextDefinitions->subject : '';

        $body = isset($contextDefinitions->body) ? $contextDefinitions->body : '';

        return array(
            'subject' => $subject,
            'layout' => $layout,
            'body' => $body
        );
    }

    public static function prepareEmailBody($emailDefinitions, $searches, $replaces){
        $body = str_replace(':body', $emailDefinitions['body'], $emailDefinitions['layout']);
        return $searches != null ? str_replace($searches, $replaces, $body) : $body;
    }

    public static function createQuicklyWithRecipients($context, $subject, $body, $recipients){
        $definitions = self::getDefaultEmailDefinitions();

        $subject = $definitions->subject . " $subject";
        $formatedRecipients = array();

        foreach($recipients as $recipient) $formatedRecipients[] = array('Address' => $recipient);

        $EmailMessage = new Model_EmailMessage();
        return $EmailMessage->createWithRecipients($context, $definitions->sender_name, $definitions->sender_address,
            $subject, $body, $formatedRecipients);
    }

    public static function getDefaultEmailDefinitions(){
        return Zend_Registry::get('email_definitions')->default;
    }

}