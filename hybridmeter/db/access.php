// This file is part of Moodle - http://moodle.org
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @author Nassim Bennouar
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
 * @package
 */
defined('MOODLE_INTERNAL') || die();

/*$capabilities = array(
    'report/hybridmeter:view' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'read',
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),
        'contextlevel' => CONTEXT_MODULE,
    ),
    'report/hybridmeter:download' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'read',
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),
        'contextlevel' => CONTEXT_MODULE,
    ),
    'report/hybridmeter:set_up' => array(
        'riskbitmask' => RISK_SPAM,
        'captype' => 'write',
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),
        'contextlevel' => CONTEXT_MODULE,
    )
);*/

$capabilities = [
    'report/hybridmeter:all' => [
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'read',
        'archetypes' => [
            'manager' => CAP_ALLOW,
        ],
        'contextlevel' => CONTEXT_MODULE,
    ],
];
