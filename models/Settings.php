<?php namespace ServiusHack\PostToFacebook\models;

use Model;
use Cms\Classes\Theme;
use Cms\Classes\Page;

class Settings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'serviushack_posttofacebook_settings';

    public $settingsFields = 'fields.yaml';

    protected $cache = [];

    public function beforeValidate()
    {
        if (!$theme = Theme::getEditTheme())
            throw new ApplicationException('Unable to find the active theme.');

        $themeMap = $this->getSettingsValue('theme_map', []);
        $themeMap[$theme->getDirName()] = $this->getSettingsValue('cms_page');
        $this->setSettingsValue('theme_map', $themeMap);
    }

    public function afterFetch()
    {
        if (
            ($theme = Theme::getEditTheme())
            && ($themeMap = array_get($this->attributes, 'theme_map'))
            && ($cmsPage = array_get($themeMap, $theme->getDirName()))
        ) {
            $this->cms_page = $cmsPage;
        }
    }

    public function getPostPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }
}
