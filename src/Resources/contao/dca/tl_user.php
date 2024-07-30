<?php

use Alnv\ContaoBackendUserNotificationBundle\Library\Helpers;
use Contao\Backend;
use Contao\BackendUser;
use Contao\Config;
use Contao\Controller;
use Contao\Database;
use Contao\Email;
use Contao\Environment;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Contao\UserModel;

$GLOBALS['TL_DCA']['tl_user']['config']['onload_callback'][] = function () {

    $strNotify = Input::get('notify') ?: '';

    if (!$strNotify) {
        return;
    }

    if ($strNotify == 'password') {

        $objUser = UserModel::findByPk(Input::get('id'));
        $strPassword = Helpers::generatePassword();
        $simpleTokenParser = System::getContainer()->get('contao.string.simple_token_parser');
        $objPasswordHasher = System::getContainer()->get('security.password_hasher_factory')->getPasswordHasher(BackendUser::class);

        $arrSet = [
            'disable' => 0,
            'pwChange' => 1,
            'tstamp' => time(),
            'password' => $objPasswordHasher->hash($strPassword)
        ];

        $arrTokens = [
            'name' => $objUser->name,
            'email' => $objUser->email,
            'username' => $objUser->username,
            'password' => $strPassword,
            'domain' => Environment::get('url') . '/contao'
        ];

        $strSubject = $simpleTokenParser->parse((Config::get('emailSubject') ?? ''), $arrTokens);
        $strText = $simpleTokenParser->parse((Config::get('emailText') ?? ''), $arrTokens);

        Database::getInstance()
            ->prepare('UPDATE tl_user %s WHERE id=?')
            ->set($arrSet)
            ->limit(1)
            ->execute($objUser->id);

        $objEmail = new Email();
        $objEmail->fromName = Config::get('adminEmail');
        $objEmail->subject = System::getContainer()->get('contao.insert_tag.parser')->replaceInline($strSubject);
        $objEmail->text = System::getContainer()->get('contao.insert_tag.parser')->replaceInline($strText);
        $objEmail->html = System::getContainer()->get('contao.insert_tag.parser')->replaceInline($strText);
        $objEmail->sendTo($objUser->email);
    }

    Controller::redirect(preg_replace('/&(amp;)?notify=[^&]*/i', '', preg_replace('/&(amp;)?' . preg_quote(Input::get('notify'), '/') . '=[^&]*/i', '', Environment::get('request'))));
};

$GLOBALS['TL_DCA']['tl_user']['list']['operations']['notifyPassword'] = [
    'href' => 'notify=password',
    'icon' => 'bundles/alnvcontaobackendusernotification/send.svg',
    'button_callback' => ['tl_user_notify', 'notifyUser']
];

class tl_user_notify
{

    public function notifyUser($row, $href, $label, $title, $icon): string
    {

        if ($row['id'] == BackendUser::getInstance()->id) {
            return '';
        }

        if (!Config::get('emailText')) {
            return '';
        }

        return '<a href="' . Backend::addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . StringUtil::specialchars($title) . '">' . Image::getHtml($icon, $label) . '</a> ';
    }
}