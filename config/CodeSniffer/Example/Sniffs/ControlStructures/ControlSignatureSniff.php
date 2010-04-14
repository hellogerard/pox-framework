<?php



class Zipscene_Sniffs_ControlStructures_ControlSignatureSniff extends PHP_CodeSniffer_Standards_AbstractPatternSniff
{


    public function __construct()
    {
        parent::__construct(true);

    }


    protected function getPatterns()
    {
        return array(
            'tryEOL',
            'catch (...)EOL',
            '} while (...);EOL',
            'while (...)EOL',
            'for (...)EOL',
            'foreach (...)EOL',
            'if (...)EOL',
            'else if (...)EOL',
            'elseif (...)EOL',
            'elseEOL',
            'doEOL',
        );
    }
}

?>
