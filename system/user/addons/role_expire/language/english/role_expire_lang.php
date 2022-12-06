<?php

$lang = [
    'role_expire_module_name' => 'Role Expire',
    'role_expire_module_description' => '',
    'role_expire_settings' => 'Role Expire Settings',
    're.role.id' => 'ID',
    're.role.name' => 'Name',
    're.role.ttl' => 'Time To Expire',
    're.role.enabled' => 'Enabled',
    're.role.disabled' => 'Disabled',
    're.role.manage' => 'Manage',
    're.role.none' => 'None',

    're.header.list_role_expire' => 'List Roles',
    're.header.edit_role_expire' => 'Edit Role Expiration',

    're.error.update_role_expire' => 'Error Updating Role',
    're.form.role_name' => 'Role Name',
    're.form.ttl' => 'Role Expire Time',
    're.form.enabled' => 'Enabled',
    're.form.expired_role' => 'Expired Role',
    're.form.desc.expired_role' => 'If you want expired Members to be assigned to a new Role',
    're.form.header.role_details' => 'Role Details',
    're.form.header.notification' => 'Notification',
    're.form.notify_enabled' => 'Enable Notification',
    're.form.desc.notify_enabled' => 'If enabled, an email will be sent to the people designated below',
    're.form.notify_to' => 'Who to notify',
    're.form.note.notify_to' => 'Use a comma seperated list of emails. Use {email} to send to the specific user.',
    're.form.notify_subject' => 'Email Subject',
    're.form.note.notify_subject' => 'You can use basic email variables as template tags.',
    're.form.notify_body' => 'Email Body',
    're.form.note.notify_body' => 'You can use basic email variables as template tags.',

    're.form.desc.role_name' => 'The colloquial name for the Member Role we\'re modifying',
    're.form.desc.ttl' => 'How long do you want Members with this Role to stay active on the site?',
    're.form.desc.enabled' => 'Do you want the rules to apply to current Members of this Role?',
    're.form.note.ttl_custom' => 'The custom integer (in seconds) for how long the Member Role should allow access.',

    're.error.cannot_edit_super_admin' => 'Cannot Expire Super Admins',
    're.error.role_not_found' => 'Role Not Found',

    're.success.role_edited' => 'Role Expire Edited',

    're.member_role_expired_error' => 'This account has expired and you are no longer able to continue using this site.',
];
