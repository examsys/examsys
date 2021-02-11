<?php

// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Paper settings accessor methods
 * Used by PaperProperties to access settings
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2020 The University of Nottingham
 */
class PaperSettings
{
    /**
     * @var mysqli The database object
     */
    private $db;

    /**
     * @var Config The config object
     */
    private $config;

    /**
     * @var integer the paper id
     */
    private $paper;

    /**
     * @var string the paper type
     */
    private $papertype;

    /**
     * @var array Assessment types
     */
    private $types;

    /**
     * @var string Language pack component
     */
    private $langcomponent = 'classes/papersettings';

    /** @var array Array of settings */
    private $settings;

    /**
     * Called when the object is unserialised.
     */
    public function __wakeup()
    {
        // The serialised database object will be invalid,
        // this object should only be serialised during an error report,
        // so adding the current database connect seems like a waste of time.
        $this->db = null;
    }

    /**
     * Constructor.
     * @param integer $paper the paper
     * @param string $type the paper type
     */
    public function __construct(int $paper, string $type)
    {
        $configObject = Config::get_instance();
        $this->db = $configObject->db;
        $this->config = $configObject;
        $this->paper = $paper;
        $this->papertype = $type;
        $this->types = PaperUtils::getTypeList();
        $this->get();
    }

    /**
     * Update a setting
     * @param string $setting The name of the setting
     * @param string|array $value
     * @param integer $paper The paper to which this setting belongs
     * @throws coding_exception
     */
    public function updateSetting(string $setting, string $value, int $paper): void
    {
        $type = $this->getType($setting);

        // Ensure value conforms to type.
        $value = $this->verifyValue($type, $value);

        // Check if setting exists.
        $currentsetting = $this->getSetting($setting);
        if (!is_null($currentsetting)) {
            // Update Settings.
            $result = $this->db->prepare('UPDATE `paper_settings` SET `value`= ? WHERE paperid = ? AND setting = ?');
            $result->bind_param('sis', $value, $paper, $setting);
            if ($result->execute()) {
                $result->close();
            }
        } else {
            // Insert Settings.
            $result = $this->db->prepare(
                'INSERT INTO
                    `paper_settings`
                    (`paperid`, `setting`, `value`)
                VALUES (?, ?, ?)'
            );
            $result->bind_param('iss', $paper, $setting, $value);
            if ($result->execute()) {
                $result->close();
            }
        }
        // Update cache.
        $this->settings[$setting]['value'] = $value;
    }

    /**
     * Load list of paper settings
     * @return array
     */
    public function get(): array
    {
        $list = array();
        $papertype = array_search($this->papertype, $this->types);
        $sql = 'SELECT
            paper_settings_setting.setting,
            paper_settings.value,
            paper_settings_setting.category,
            paper_settings_setting.type,
            paper_settings_setting.supported->\'$.' . $papertype . '\' as supported
        FROM properties
        CROSS JOIN paper_settings_setting
        LEFT JOIN paper_settings ON properties.property_id = paper_settings.paperid
        AND paper_settings_setting.setting = paper_settings.setting
        WHERE properties.property_id = ?';
        $result = $this->db->prepare($sql);
        $result->bind_param('i', $this->paper);
        $result->execute();
        $result->store_result();
        $result->bind_result($setting, $value, $category, $type, $supported);
        while ($result->fetch()) {
            if ($supported == 1) {
                $list[$setting] = array('value' => $value, 'type' => $type, 'category' => $category);
            }
        }
        $result->close();
        // Load into cache.
        $this->settings = $list;
        return $list;
    }

    /**
     * Get a setting for a paper
     * @param string $setting the setting
     * @throws coding_exception
     * @return mixed
     */
    public function getSetting(string $setting)
    {
        // Check setting exists and get type.
        $type = $this->getType($setting);

        // Check cache.
        if (isset($this->settings[$setting])) {
            return $this->settings[$setting]['value'];
        }

        $result = $this->db->prepare(
            'SELECT
                value
            FROM 
                paper_settings
            WHERE
                setting = ? AND
                paperid =  ?'
        );
        $result->bind_param('si', $setting, $this->paper);
        $result->execute();
        $result->store_result();
        $result->bind_result($value);
        if ($result->num_rows() == 0) {
                $value = null;
        }
        $result->fetch();
        $result->close();
        $this->settings[$setting]['value'] = $value;
        return $value;
    }

    /**
     * Render paper settings by category
     * @param string $category the setting category
     */
    public function renderSettings(string $category = ''): void
    {
        $langpack = new langpack();
        $strings = $langpack->get_all_strings($this->langcomponent);
        $render = new render($this->config);
        $data = array();
        if ($category != '') {
            // Category specifc settings.
            foreach ($this->settings as $setting => $info) {
                if ($info['category'] == $category) {
                    $data[$category][$setting] = array('value' => $info['value'], 'type' => $info['type']);
                }
            }
        } else {
            // All settings.
            foreach ($this->settings as $setting => $info) {
                $data[$category][$setting] = array('value' => $info['value'], 'type' => $info['type']);
            }
        }
        $render->render($data, $strings, 'admin/paper/settings.html');
    }

    /**
     * Get setting declarations
     * @param string $papertype the paper type
     * @return array
     */
    private static function getSettingDeclartions(string $papertype): array
    {
        $declarations = array();
        $configObject = Config::get_instance();
        $db = $configObject->db;
        $result = $db->prepare(
            'SELECT
                setting,
                category,
                type,
                supported->\'$.' . $papertype . '\' as supported
            FROM 
                paper_settings_setting'
        );
        $result->execute();
        $result->store_result();
        $result->bind_result($setting, $category, $type, $supported);
        while ($result->fetch()) {
            if ($supported == 1) {
                $declarations[$category][$setting] = array('type' => $type, 'supported' => $supported);
            }
        }
        $result->close();
        return $declarations;
    }

    /**
     * Render paper settings by category
     * @param array $strings language strings
     * @param string $papertype the paper type
     * @param string $category the setting category
     */
    public static function renderNewSettings(array $strings, string $papertype, string $category = ''): void
    {
        $declarations = self::getSettingDeclartions($papertype);
        $data = array();
        if (count($declarations) > 0) {
            if ($category === '') {
                // All settings.
                $data = $declarations;
            } elseif (isset($declarations[$category])) {
                // Category specifc settings.
                $data[$category] = $declarations[$category];
            }
        }
        $render = new render(Config::get_instance());
        $render->render($data, $strings, 'admin/paper/settings.html');
    }

    /**
     * Check it settings category is enabled
     * @param string $category the setting category
     * @return boolean
     */
    public function settingsCategoryEnabled(string $category): bool
    {
        $papertype = array_search($this->papertype, $this->types);
        $declarations = self::getSettingDeclartions($papertype);
        $data = array();
        if (count($declarations) > 0) {
            $data = $declarations[$category];
        }
        if (count($data) > 0) {
            return true;
        }
        return false;
    }

    /**
     * Verify value for type
     * @param mixed $type settings type
     * @param mixed $value settings value
     * @return mixed
     */
    public function verifyValue($type, $value)
    {
        switch ($type) {
            case \Config::BOOLEAN:
                if (empty($value)) {
                    $value = 0;
                } else {
                    $value = 1;
                }
                break;
            default:
                $value = '';
        }
        return $value;
    }

    /**
     * Get setting type
     * @param string $setting the setting
     * @throws coding_exception
     * @return string|null
     */
    public function getType(string $setting)
    {
        $result = $this->db->prepare(
            'SELECT
                type
            FROM 
                paper_settings_setting
            WHERE
                setting = ?'
        );
        $result->bind_param('s', $setting);
        $result->execute();
        $result->store_result();
        $result->bind_result($type);
        if ($result->num_rows() == 0) {
            throw new coding_exception('invalid_paper_setting');
        }
        $result->fetch();
        $result->close();
        return $type;
    }

    /**
     * Create paper setting declarations
     * @param mysqli $db the database connection
     * @param string $setting the setting
     * @param string $category the setting category
     * @param string $type the setting type
     * @param string $supported the paper types that support this setting
     */
    public static function createPaperSettingsSetting(
        $db,
        string $setting,
        string $category,
        string $type,
        string $supported
    ): void {
        $insert = $db->prepare('INSERT INTO paper_settings_setting (setting, category, type, supported) VALUES (?, ?, ?, ?)');
        $insert->bind_param('ssss', $setting, $category, $type, $supported);
        $insert->execute();
        $insert->close();
    }

    /**
     * Create paper setting categories
     * @param mysqli $db the database connection
     * @param array $categories the setting categories
     */
    public static function createPaperSettingsCategories(
        $db,
        array $categories
    ): void {
        $insert = $db->prepare('INSERT INTO paper_settings_category (category) VALUES (?)');
        foreach ($categories as $category) {
            $insert->bind_param('s', $category);
            $insert->execute();
        }
        $insert->close();
    }
}
