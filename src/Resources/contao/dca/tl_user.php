<?php

$GLOBALS['TL_DCA']['tl_user']['config']['onload_callback'][] = function () {

    if (!\Input::get('notify')) {
        return null;
    }

    $strNotify = \Input::get('notify') ?: '';

    if ($strNotify == 'password') {

        $objUser = \UserModel::findByPk(\Input::get('id'));
        $strPassword = \Alnv\ContaoBackendUserNotificationBundle\Library\Helpers::generatePassword();
        $objPasswordHasher = \System::getContainer()->get('security.password_hasher_factory')->getPasswordHasher(\BackendUser::class);

        $arrSet = [
            'disable' => '',
            'pwChange' => '1',
            'tstamp' => time(),
            'password' => $objPasswordHasher->hash($strPassword)
        ];

        $arrTokens = [
            'name' => $objUser->name,
            'email' => $objUser->email,
            'username' => $objUser->username,
            'password' => $strPassword,
            'domain' => \Environment::get('url') . '/contao'
        ];

        $strSubject = \StringUtil::parseSimpleTokens(\Config::get('emailSubject'), $arrTokens) ?: '';
        $strText = \StringUtil::parseSimpleTokens(\Config::get('emailText'), $arrTokens) ?: '';

        $objEmail = new Email();
        $objEmail->fromName = \Config::get('adminEmail');
        $objEmail->subject = \Controller::replaceInsertTags($strSubject);
        $objEmail->text = \Controller::replaceInsertTags($strText);
        $objEmail->html = \Controller::replaceInsertTags($strText);
        $objEmail->sendTo($objUser->email);

        \Database::getInstance()->prepare('UPDATE tl_user %s WHERE id=?')->set($arrSet)->limit(1)->execute($objUser->id);
    }

    \Controller::redirect(preg_replace('/&(amp;)?notify=[^&]*/i', '', preg_replace( '/&(amp;)?' . preg_quote(\Input::get('notify'), '/') . '=[^&]*/i', '', \Environment::get('request'))));
};

$GLOBALS['TL_DCA']['tl_user']['list']['operations']['notifyPassword'] = [
    'href' => 'notify=password',
    'icon' => 'bundles/alnvcontaobackendusernotification/send.svg',
    'button_callback' => ['tl_user_notify', 'notifyUser']
];

class tl_user_notify {

    public function notifyUser($row, $href, $label, $title, $icon) {

        if ($row['id'] == \BackendUser::getInstance()->id) {
            return '';
        }

        return '<a href="' . \Backend::addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . StringUtil::specialchars($title) . '">' . Image::getHtml($icon, $label) . '</a> ';
    }
}