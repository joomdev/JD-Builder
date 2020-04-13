<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JDPageBuilder;

// No direct access
defined('_JEXEC') or die('Restricted access');

class Constants
{

    const VERSION = 'Pro 3.2.1';
    const ACTIVE = 1;
    const INACTIVE = 0;
    const DELETED = 2;
    const FONTAWESOME_VERSION = '5.13.0';
    const LOGO = 'data:image/svg+xml;charset=UTF-8,%3Csvg%20version%3D%221.1%22%20baseProfile%3D%22tiny%22%20id%3D%22Layer_1%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20xmlns%3Axlink%3D%22http%3A%2F%2Fwww.w3.org%2F1999%2Fxlink%22%0A%09%20x%3D%220px%22%20y%3D%220px%22%20width%3D%221054.447px%22%20height%3D%22191.081px%22%20viewBox%3D%220%200%201054.447%20191.081%22%20xml%3Aspace%3D%22preserve%22%3E%0A%3Cg%3E%0A%09%3Cg%3E%0A%09%09%3Cpath%20fill%3D%22currentColor%22%20d%3D%22M270.192%2C186.766V12.57h27.389c15.887%2C0%2C27.506%2C1.027%2C34.857%2C3.079%0A%09%09%09c10.434%2C2.765%2C18.734%2C7.896%2C24.899%2C15.395c6.166%2C7.501%2C9.248%2C16.342%2C9.248%2C26.526c0%2C6.631-1.402%2C12.652-4.204%2C18.059%0A%09%09%09c-2.803%2C5.409-7.362%2C10.48-13.677%2C15.217c10.578%2C4.974%2C18.314%2C11.19%2C23.21%2C18.651c4.894%2C7.46%2C7.342%2C16.283%2C7.342%2C26.467%0A%09%09%09c0%2C9.79-2.529%2C18.71-7.588%2C26.763c-5.059%2C8.052-11.577%2C14.073-19.56%2C18.059c-7.982%2C3.987-19.009%2C5.98-33.074%2C5.98H270.192z%0A%09%09%09%20M303.349%2C44.188v36.71h7.252c8.082%2C0%2C14.086-1.697%2C18.009-5.092c3.923-3.394%2C5.884-8.012%2C5.884-13.855%0A%09%09%09c0-5.447-1.863-9.77-5.588-12.967s-9.391-4.796-16.997-4.796H303.349z%20M303.349%2C110.503v44.644h8.312%0A%09%09%09c13.774%2C0%2C23.057-1.735%2C27.846-5.21c4.789-3.473%2C7.185-8.526%2C7.185-15.158c0-7.499-2.811-13.42-8.432-17.763%0A%09%09%09c-5.621-4.341-14.962-6.513-28.023-6.513H303.349z%22%2F%3E%0A%09%09%3Cpath%20fill%3D%22currentColor%22%20d%3D%22M407.204%2C57.925h32.684v62.052c0%2C12.079%2C0.831%2C20.468%2C2.492%2C25.164c1.662%2C4.698%2C4.334%2C8.349%2C8.014%2C10.954%0A%09%09%09c3.679%2C2.605%2C8.21%2C3.908%2C13.593%2C3.908c5.381%2C0%2C9.951-1.282%2C13.711-3.849c3.76-2.565%2C6.548-6.335%2C8.369-11.309%0A%09%09%09c1.345-3.71%2C2.019-11.644%2C2.019-23.803V57.925h32.328v54.592c0%2C22.5-1.776%2C37.894-5.329%2C46.184%0A%09%09%09c-4.342%2C10.106-10.737%2C17.863-19.184%2C23.27c-8.448%2C5.407-19.184%2C8.112-32.21%2C8.112c-14.133%2C0-25.561-3.159-34.283-9.474%0A%09%09%09c-8.724-6.315-14.861-15.117-18.414-26.408c-2.527-7.816-3.789-22.026-3.789-42.631V57.925z%22%2F%3E%0A%09%09%3Cpath%20fill%3D%22currentColor%22%20d%3D%22M565.472%2C4.873c5.667%2C0%2C10.527%2C2.054%2C14.582%2C6.158c4.054%2C4.106%2C6.082%2C9.08%2C6.082%2C14.921%0A%09%09%09c0%2C5.764-2.008%2C10.678-6.023%2C14.743c-4.015%2C4.067-8.816%2C6.099-14.404%2C6.099c-5.747%2C0-10.648-2.072-14.702-6.217%0A%09%09%09c-4.055-4.145-6.08-9.177-6.08-15.098c0-5.684%2C2.005-10.54%2C6.021-14.566S559.802%2C4.873%2C565.472%2C4.873z%20M549.307%2C57.925h32.329%0A%09%09%09v128.841h-32.329V57.925z%22%2F%3E%0A%09%09%3Cpath%20fill%3D%22currentColor%22%20d%3D%22M607.452%2C8.188h32.329v178.577h-32.329V8.188z%22%2F%3E%0A%09%09%3Cpath%20fill%3D%22currentColor%22%20d%3D%22M764.239%2C8.188h32.329v178.577h-32.329v-13.618c-6.31%2C6-12.638%2C10.323-18.983%2C12.967%0A%09%09%09c-6.349%2C2.644-13.229%2C3.967-20.641%2C3.967c-16.638%2C0-31.029-6.454-43.173-19.362s-18.215-28.954-18.215-48.138%0A%09%09%09c0-19.895%2C5.875-36.196%2C17.624-48.907c11.75-12.71%2C26.021-19.065%2C42.818-19.065c7.727%2C0%2C14.979%2C1.461%2C21.762%2C4.381%0A%09%09%09c6.781%2C2.921%2C13.05%2C7.303%2C18.808%2C13.145V8.188z%20M730.254%2C84.451c-9.993%2C0-18.294%2C3.534-24.903%2C10.598%0A%09%09%09c-6.609%2C7.066-9.914%2C16.125-9.914%2C27.178c0%2C11.131%2C3.362%2C20.29%2C10.09%2C27.473c6.728%2C7.185%2C15.008%2C10.776%2C24.845%2C10.776%0A%09%09%09c10.15%2C0%2C18.569-3.532%2C25.256-10.599c6.688-7.064%2C10.033-16.322%2C10.033-27.77c0-11.209-3.346-20.289-10.033-27.236%0A%09%09%09C748.941%2C87.926%2C740.483%2C84.451%2C730.254%2C84.451z%22%2F%3E%0A%09%09%3Cpath%20fill%3D%22currentColor%22%20d%3D%22M958.33%2C131.7H854.475c1.5%2C9.159%2C5.508%2C16.442%2C12.02%2C21.849c6.514%2C5.409%2C14.822%2C8.112%2C24.928%2C8.112%0A%09%09%09c12.078%2C0%2C22.459-4.223%2C31.145-12.671l27.236%2C12.789c-6.791%2C9.633-14.92%2C16.757-24.395%2C21.375%0A%09%09%09c-9.473%2C4.618-20.723%2C6.928-33.75%2C6.928c-20.211%2C0-36.67-6.375-49.38-19.125c-12.712-12.749-19.065-28.717-19.065-47.901%0A%09%09%09c0-19.658%2C6.335-35.979%2C19.006-48.966c12.67-12.986%2C28.557-19.48%2C47.665-19.48c20.289%2C0%2C36.787%2C6.494%2C49.5%2C19.48%0A%09%09%09c12.709%2C12.987%2C19.064%2C30.138%2C19.064%2C51.453L958.33%2C131.7z%20M926%2C106.24c-2.135-7.183-6.346-13.026-12.633-17.526%0A%09%09%09s-13.582-6.75-21.887-6.75c-9.014%2C0-16.922%2C2.528-23.723%2C7.579c-4.27%2C3.159-8.225%2C8.724-11.861%2C16.697H926z%22%2F%3E%0A%09%09%3Cpath%20fill%3D%22currentColor%22%20d%3D%22M976.92%2C57.925h27.711v16.224c3-6.395%2C6.986-11.25%2C11.961-14.565c4.973-3.316%2C10.42-4.974%2C16.342-4.974%0A%09%09%09c4.184%2C0%2C8.564%2C1.106%2C13.145%2C3.315l-10.066%2C27.829c-3.789-1.895-6.908-2.842-9.355-2.842c-4.973%2C0-9.176%2C3.079-12.611%2C9.237%0A%09%09%09c-3.434%2C6.158-5.15%2C18.236-5.15%2C36.236l0.117%2C6.276v52.104H976.92V57.925z%22%2F%3E%0A%09%3C%2Fg%3E%0A%09%3Crect%20x%3D%2270.256%22%20y%3D%2257.593%22%20fill%3D%22currentColor%22%20width%3D%2233.231%22%20height%3D%22121.821%22%2F%3E%0A%09%3Crect%20x%3D%2213.166%22%20y%3D%22141.585%22%20fill%3D%22currentColor%22%20width%3D%2233.231%22%20height%3D%2235.818%22%2F%3E%0A%09%3Crect%20x%3D%22170.567%22%20y%3D%2213.425%22%20fill%3D%22currentColor%22%20width%3D%2233.229%22%20height%3D%22165.989%22%2F%3E%0A%09%3Crect%20x%3D%2213.166%22%20y%3D%22153.651%22%20fill%3D%22currentColor%22%20width%3D%2290.321%22%20height%3D%2233.231%22%2F%3E%0A%09%3Crect%20x%3D%22120.8%22%20y%3D%22153.651%22%20fill%3D%22currentColor%22%20width%3D%2282.996%22%20height%3D%2233.231%22%2F%3E%0A%09%3Crect%20x%3D%22120.8%22%20y%3D%2212.417%22%20fill%3D%22currentColor%22%20width%3D%2282.996%22%20height%3D%2233.231%22%2F%3E%0A%09%3Ccircle%20fill%3D%22currentColor%22%20cx%3D%2287.666%22%20cy%3D%2225.807%22%20r%3D%2220.898%22%2F%3E%0A%3C%2Fg%3E%0A%3C%2Fsvg%3E';
    const LOGOTEXT = '';
    const JSTYLES = 'html *,html *::before,html *::after{box-sizing:inherit}';
    const PRIMARY_COLOR = '#fff';
    const ELEMENT_BGCOLOR = '#464ed2';
    const ELEMENT_BORDERCOLOR = 'rgba(0, 0, 0, 0.30)';
    const FORMS_CACHE_DIR = '_jdbforms';
    const CSS_CACHE_DIR = '_jdbcss';
    const JS_CACHE_DIR = '_jdbjs';
    const ANIMATIONS = [
        'Attention Seekers' => [
            'bounce' => 'bounce',
            'flash' => 'flash',
            'pulse' => 'pulse',
            'rubberBand' => 'rubberBand',
            'shake' => 'shake',
            'swing' => 'swing',
            'tada' => 'tada',
            'wobble' => 'wobble',
            'jello' => 'jello',
            'heartBeat' => 'heartBeat'
        ],
        'Bouncing Entrances' => [
            'bounceIn' => 'bounceIn',
            'bounceInDown' => 'bounceInDown',
            'bounceInLeft' => 'bounceInLeft',
            'bounceInRight' => 'bounceInRight',
            'bounceInUp' => 'bounceInUp'
        ],
        'Fading Entrances' => [
            'fadeIn' => 'fadeIn',
            'fadeInDown' => 'fadeInDown',
            'fadeInDownBig' => 'fadeInDownBig',
            'fadeInLeft' => 'fadeInLeft',
            'fadeInLeftBig' => 'fadeInLeftBig',
            'fadeInRight' => 'fadeInRight',
            'fadeInRightBig' => 'fadeInRightBig',
            'fadeInUp' => 'fadeInUp',
            'fadeInUpBig' => 'fadeInUpBig'
        ],
        'Flippers Entrances' => [
            'flip' => 'flip',
            'flipInX' => 'flipInX',
            'flipInY' => 'flipInY'
        ],
        'Lightspeed Entrances' => [
            'lightSpeedIn' => 'lightSpeedIn',
        ],
        'Rotating Entrances' => [
            'rotateIn' => 'rotateIn',
            'rotateInDownLeft' => 'rotateInDownLeft',
            'rotateInDownRight' => 'rotateInDownRight',
            'rotateInUpLeft' => 'rotateInUpLeft',
            'rotateInUpRight' => 'rotateInUpRight'
        ],
        'Sliding Entrances' => [
            'slideInUp' => 'slideInUp',
            'slideInDown' => 'slideInDown',
            'slideInLeft' => 'slideInLeft',
            'slideInRight' => 'slideInRight',
        ],
        'Zoom Entrances' => [
            'zoomIn' => 'zoomIn',
            'zoomInDown' => 'zoomInDown',
            'zoomInLeft' => 'zoomInLeft',
            'zoomInRight' => 'zoomInRight',
            'zoomInUp' => 'zoomInUp',
        ],
        'Specials' => [
            'jackInTheBox' => 'jackInTheBox',
            'rollIn' => 'rollIn',
        ],
    ];
    const HOVER_ANIMATIONS = [
        "grow" => "Grow",
        "shrink" => "Shrink",
        "pulse" => "Pulse",
        "pulse-grow" => "Pulse Grow",
        "pulse-shrink" => "Pulse Shrink",
        "push" => "Push",
        "pop" => "Pop",
        "bounce-in" => "Bounce In",
        "bounce-out" => "Bounce Out",
        "rotate" => "Rotate",
        "grow-rotate" => "Grow Rotate",
        "float" => "Float",
        "sink" => "Sink",
        "bob" => "Bob",
        "hang" => "Hang",
        "skew" => "Skew",
        "skew-forward" => "Skew Forward",
        "skew-backward" => "Skew Backward",
        "wobble-horizontal" => "Wobble Horizontal",
        "wobble-vertical" => "Wobble Vertical",
        "wobble-to-bottom-right" => "Wobble To Bottom Right",
        "wobble-to-top-right" => "Wobble To Top Right",
        "wobble-top" => "Wobble Top",
        "wobble-bottom" => "Wobble Bottom",
        "wobble-skew" => "Wobble Skew",
        "buzz" => "Buzz",
        "buzz-out" => "Buzz Out",
        "forward" => "Forward",
        "backward" => "Backward",
    ];
    const ICON_HOVER_ANIMATIONS = [
        "icon-back",
        "icon-forward",
        "icon-down",
        "icon-up",
        "icon-spin",
        "icon-drop",
        "icon-fade",
        "icon-float-away",
        "icon-sink-away",
        "icon-grow",
        "icon-shrink",
        "icon-pulse",
        "icon-pulse-grow",
        "icon-pulse-shrink",
        "icon-push",
        "icon-pop",
        "icon-bounce",
        "icon-rotate",
        "icon-grow-rotate",
        "icon-float",
        "icon-sink",
        "icon-bob",
        "icon-hang",
        "icon-wobble-horizontal",
        "icon-wobble-vertical",
        "icon-buzz",
        "icon-buzz-out"
    ];
    const SYSTEM_FONTS = [
        "Arial, Helvetica, sans-serif" => 'Arial, Helvetica',
        "Arial Black, Gadget, sans-serif" => 'Arial Black, Gadget',
        "Bookman Old Style, serif" => 'Bookman Old Style',
        "Comic Sans MS, cursive" => 'Comic Sans MS',
        "Courier, monospace" => 'Courier',
        "Garamond, serif" => 'Garamond',
        "Georgia, serif" => 'Georgia',
        "Impact, Charcoal, sans-serif" => 'Impact, Charcoal',
        "Lucida Console, Monaco, monospace" => 'Lucida Console, Monaco',
        "Lucida Sans Unicode, sans-serif" => 'Lucida Sans Unicode',
        "MS Sans Serif, Geneva, sans-serif" => 'MS Sans Serif, Geneva',
        "MS Serif, New York, sans-serif" => 'MS Serif, New York',
        "Palatino Linotype, Book Antiqua, Palatino, serif" => 'Palatino Linotype, Book Antiqua, Palatino',
        "Tahoma, Geneva, sans-serif" => 'Tahoma, Geneva',
        "Times New Roman, Times, serif" => 'Times New Roman, Times',
        "Trebuchet MS, Helvetica, sans-serif" => 'Trebuchet MS, Helvetica',
        "Verdana, Geneva, sans-serif" => 'Verdana, Geneva'
    ];
    const SMART_TAGS = [
        'Joomla' => [
            'Page' => [
                // 'Page Title' => '{page.title}',
                'Page URL' => '{page.url}',
                'Page Path' => '{page.path}',
                'Page Lang' => '{page.lang}',
            ],
            'Site' => [
                'Site Name' => '{site.name}',
                'Site URL' => '{site.url}',
                'Site Host' => '{site.host}',
                'Site Email' => '{site.email}',
            ],
            'User' => [
                'User ID' => '{user.id}',
                'Username' => '{user.username}',
                'User Email' => '{user.email}',
                'User Name' => '{user.name}',
                'User Group(s)' => '{user.groups}',
            ]
        ],
        'Client' => [
            'Client' => [
                // 'Device Type' => '{client.device}',
                'Client IP' => '{client.ip}',
                'Client Browser' => '{client.browser}',
                'Client Browser Version' => '{client.browser_version}',
                'Client Operating System' => '{client.os}',
                'Client Operating System Version' => '{client.os_version}',
                'User Agent' => '{client.useragent}',
            ],
            'Device' => []
        ],
        'Others' => [
            'System' => [
                'Date (Y-m-d)' => '{system.date}',
                'Time (H:i:s)' => '{system.time}',
                'Timestamp (Y-m-d H:i:s)' => '{system.timestamp}',
            ],
            'Others' => [
                'Referrer' => '{referrer}',
                'Random ID' => '{randomid}',
                'Query String' => '{querystring.YOUR_KEY}',
            ]
        ]
    ];
}
