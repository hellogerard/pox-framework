<?php


class PHP_CodeSniffer_Standards_Example_ExampleCodingStandard extends PHP_CodeSniffer_Standards_CodingStandard
{

    public function getIncludedSniffs()
    {
        return array(
            'Zend',
            'Generic/Sniffs/Functions/OpeningFunctionBraceBsdAllmanSniff.php',
            'Generic/Sniffs/ControlStructures/InlineControlStructureSniff.php',
        );
    }

    public function getExcludedSniffs()
    {
        return array(
            'Zend/Sniffs/Debug/CodeAnalyzerSniff.php',
            'Zend/Sniffs/NamingConventions/ValidVariableNameSniff.php',
            'PEAR/Sniffs/ControlStructures/ControlSignatureSniff.php',
            'PEAR/Sniffs/Files/LineEndingsSniff.php',
        );
    }
}

?>
