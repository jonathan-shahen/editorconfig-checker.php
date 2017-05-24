<?php

namespace EditorconfigChecker\Validation;

use EditorconfigChecker\Cli\Logger;
use EditorconfigChecker\Fix\FinalNewlineFix;

class FinalNewlineValidator
{
    /**
     * Checks a file for final newline if needed
     *
     * @param array $rules
     * @param string $filename
     * @param array $content
     * @return boolean
     */
    public static function validate($rules, $filename, $content, $autoFix)
    {
        if (isset($rules['insert_final_newline']) && $rules['insert_final_newline'] && count($content)) {
            $lastLine = $content[count($content) - 1];

            if (isset($rules['end_of_line'])) {
                if ($rules['end_of_line'] === 'lf') {
                    /* I didn't get it to work properly without checking for both */
                    preg_match('/(.*\n\Z)/', $lastLine, $matchesLF);
                    preg_match('/(.*\r\n\Z)/', $lastLine, $matchesCRLF);
                    $error = !isset($matchesLF[1]) ^ isset($matchesCRLF[1]);
                } elseif ($rules['end_of_line'] === 'cr') {
                    /* I didn't get it to work properly without checking for both */
                    preg_match('/(.*\r\Z)/', $lastLine, $matchesCR);
                    preg_match('/(.*\r\n\Z)/', $lastLine, $matchesCRLF);
                    $error = !isset($matchesCR[1]) ^ isset($matchesCRLF[1]);
                } elseif ($rules['end_of_line'] === 'crlf') {
                    preg_match('/(.*\r\n\Z)/', $lastLine, $matches);
                    $error = !isset($matches[1]);
                }
            } else {
                preg_match('/(.*\n\Z)/', $lastLine, $matchesLF);
                preg_match('/(.*\r\Z)/', $lastLine, $matchesCR);
                preg_match('/(.*\r\n\Z)/', $lastLine, $matchesCRLF);
                $error = !(isset($matchesLF[1]) || isset($matchesCR[1]) || isset($matchesCRLF[1]));
            }

            if ($error) {
                Logger::getInstance()->addError('Missing final newline', $filename);

                // @TODO only if autofix
                // AND use utility
                $eolChar = $rules['end_of_line'] == 'lf' ? "\n" : ($rules['end_of_line'] == 'cr' ? "\r" : "\r\n");
                if ($autoFix && FinalNewlineFix::insert($filename, $eolChar)) {
                    Logger::getInstance()->errorFixed();
                }

                return false;
            }
        }

        return true;
    }
}
