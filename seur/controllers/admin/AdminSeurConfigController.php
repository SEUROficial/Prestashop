<?php
/*
	*  Please read the terms of the CLUF license attached to this module(cf "licences" folder)
	*
	* @author    Línea Gráfica E.C.E. S.L.
	* @copyright Lineagrafica.es - Línea Gráfica E.C.E. S.L. all rights reserved.
	* @license   https://www.lineagrafica.es/licenses/license_en.pdf https://www.lineagrafica.es/licenses/license_es.pdf https://www.lineagrafica.es/licenses/license_fr.pdf
*/

class AdminSeurConfigController extends ModuleAdminController{

    public function __construct()
    {
        Tools::redirectAdmin('index.php?controller=adminmodules&configure=seur&token='.Tools::getAdminTokenLite('AdminModules').'&module_name=seur&settings=1');
    }
}

