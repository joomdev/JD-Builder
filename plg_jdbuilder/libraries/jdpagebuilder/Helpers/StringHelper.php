<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JDPageBuilder\Helpers;

// No direct access
defined('_JEXEC') or die('Restricted access');

const rsAstralRange = '\\x{e800}-\\x{efff}';
const rsComboMarksRange = '\\x{0300}-\\x{036f}';
const reComboHalfMarksRange = '\\x{fe20}-\\x{fe2f}';
const rsComboSymbolsRange = '\\x{20d0}-\\x{20ff}';
const rsComboRange = rsComboMarksRange . reComboHalfMarksRange . rsComboSymbolsRange;
const rsDingbatRange = '\\x{2700}-\\x{27bf}';
const rsLowerRange = 'a-z\\xdf-\\xf6\\xf8-\\xff';
const rsMathOpRange = '\\xac\\xb1\\xd7\\xf7';
const rsNonCharRange = '\\x00-\\x2f\\x3a-\\x40\\x5b-\\x60\\x7b-\\xbf';
const rsPunctuationRange = '\\x{2000}-\\x{206f}';
const rsSpaceRange = ' \\t\\x0b\\f\\xa0\\x{feff}\\n\\r\\x{2028}\\x{2029}\\x{1680}\\x{180e}\\x{2000}\\x{2001}\\x{2002}\\x{2003}\\x{2004}\\x{2005}\\x{2006}\\x{2007}\\x{2008}\\x{2009}\\x{200a}\\x{202f}\\x{205f}\\x{3000}';
const rsUpperRange = 'A-Z\\xc0-\\xd6\\xd8-\\xde';
const rsVarRange = '\\x{fe0e}\\x{fe0f}';
const rsBreakRange = rsMathOpRange . rsNonCharRange . rsPunctuationRange . rsSpaceRange;

const rsApos = "[\\x{2019}]";
const rsBreak = '[' . rsBreakRange . ']';
const rsCombo = '[' . rsComboRange . ']';
const rsDigits = '\\d+';
const rsDingbat = '[' . rsDingbatRange . ']';
const rsLower = '[' . rsLowerRange . ']';
const rsMisc = '[^' . rsAstralRange . rsBreakRange . rsDigits . rsDingbatRange . rsLowerRange . rsUpperRange . ']';
const rsFitz = '\\x{e83c}[\\x{effb}-\\x{efff}]';
const rsModifier = '(?:' . rsCombo . '|' . rsFitz . ')';
const rsNonAstral = '[^' . rsAstralRange . ']';
const rsRegional = '(?:\\x{e83c}[\\x{ede6}-\\x{edff}]){2}';
const rsSurrPair = '[\\x{e800}-\\x{ebff}][\\x{ec00}-\\x{efff}]';
const rsUpper = '[' . rsUpperRange . ']';
const rsZWJ = '\\x{200d}';

/** Used to compose unicode regexes. */
const rsMiscLower = '(?:' . rsLower . '|' . rsMisc . ')';
const rsMiscUpper = '(?:' . rsUpper . '|' . rsMisc . ')';
const rsOptContrLower = '(?:' . rsApos . '(?:d|ll|m|re|s|t|ve))?';
const rsOptContrUpper = '(?:' . rsApos . '(?:D|LL|M|RE|S|T|VE))?';
const reOptMod = rsModifier . '?';
const rsOptVar = '[' . rsVarRange . ']?';
define('rsOptJoin', '(?:' . rsZWJ . '(?:' . implode('|', [rsNonAstral, rsRegional, rsSurrPair]) . ')' . rsOptVar . reOptMod . ')*');
const rsOrdLower = '\\d*(?:(?:1st|2nd|3rd|(?![123])\\dth)\\b)';
const rsOrdUpper = '\\d*(?:(?:1ST|2ND|3RD|(?![123])\\dTH)\\b)';
const rsSeq = rsOptVar . reOptMod . rsOptJoin;
define('rsEmoji', '(?:' . implode('|', [rsDingbat, rsRegional, rsSurrPair]) . ')' . rsSeq);

const rsAstral = '[' . rsAstralRange . ']';
const rsNonAstralCombo = rsNonAstral . rsCombo . '?';
define('rsSymbol', '(?:' . implode('|', [rsNonAstralCombo, rsCombo, rsRegional, rsSurrPair, rsAstral]) . ')');

const reUnicode = rsFitz . '(?=' . rsFitz . ')|' . rsSymbol . rsSeq;

class StringHelper
{
    public static function unicodeWords(string $string): array
    {
        $regex = '#' . \implode('|', [
            rsUpper . '?' . rsLower . '+' . rsOptContrLower . '(?=' . \implode('|', [rsBreak, rsUpper, '$']) . ')',
            rsMiscUpper . '+' . rsOptContrUpper . '(?=' . \implode('|', [rsBreak, rsUpper . rsMiscLower, '$']) . ')',
            rsUpper . '?' . rsMiscLower . '+' . rsOptContrLower,
            rsUpper . '+' . rsOptContrUpper,
            rsOrdUpper,
            rsOrdLower,
            rsDigits,
            rsEmoji,
        ]) . '#u';

        if (\preg_match_all($regex, $string, $matches) > 0) {
            return $matches[0];
        }

        return [];
    }

    public static function words(string $string, string $pattern = null): array
    {
        $hasUnicodeWord = '/[a-z][A-Z]|[A-Z]{2,}[a-z]|[0-9][a-zA-Z]|[a-zA-Z][0-9]|[^a-zA-Z0-9 ]/';
        $asciiWords = '/[^\x00-\x2f\x3a-\x40\x5b-\x60\x7b-\x7f]+/';

        if (null === $pattern) {
            if (\preg_match($hasUnicodeWord, $string)) {
                return self::unicodeWords($string);
            }

            \preg_match_all($asciiWords, $string, $matches);

            return $matches[0] ?? [];
        }

        if (\preg_match_all($pattern, $string, $matches) > 0) {
            return $matches[0];
        }

        return [];
    }

    public static function kebabCase($string)
    {
        return \implode('-', \array_map('\strtolower', self::words(\preg_replace("/['\x{2019}]/u", '', $string))));
    }
}
