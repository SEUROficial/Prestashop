/**
* 2007-2017 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2017 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*
* Don't forget to prefix your containers with your own identifier
* to avoid any conflicts with others containers.
*/

$(document).ready(function () {
    var hide_others = false;

    /********* PS 1.7 ******************/
    var payment_forms = $("form#payment-form");
    payment_forms.each(function(index) {
        var aux = $(this).attr('action');
        if (is_seur_cod(aux)) {
            hide_others = true;
        }
    });
    payment_forms.each(function(index) {
        var aux = $(this).attr('action');
        if (hide_others && is_cod(aux)) {
            $('#payment-option-'+eval(index+1)+'-container').hide();
        }
    });

    /******** PS 1.6 ********************/
    var payment_modules = $(".payment_module");
    payment_modules.each(function(index) {
        var aux = $(this).find('a').attr('href');
        if (is_seur_cod(aux)) {
            hide_others = true;
        }
    });
    payment_modules.each(function(index) {
        var aux = $(this).find('a').attr('href');
        if (hide_others && is_cod(aux)) {
            $(this).hide();
        }
    });

    /******* common functions **********/
    function is_seur_cod(aux) {
        return aux.search('seurcashondelivery') != -1;
    }

    function is_cod(aux) {
        return (aux.search('cod') !== -1 ||
            aux.search('cashondelivery') !== -1 ||
            aux.search('reembolso') !== -1 ) &&
            (aux.search('seurcashondelivery') == -1);
    }
});