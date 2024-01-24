{assign var="total_expedidiciones" value=0}
{assign var="total_bultos" value=0}
{assign var="total_kgs" value=0}
{assign var="total_reembolso" value=0}
{assign var="total_seguro" value=0}
{assign var="total_valor_rs" value=0}

{foreach $orders as $order}
{$total_expedidiciones = $total_expedidiciones+1}
{$total_bultos = $total_bultos + $order.bultos}
{$total_kgs = $total_kgs + $order.peso}
{$total_reembolso = $total_reembolso + $order.cashondelivery}
{/foreach}

<table style="border:2px solid #000; padding:2px">
    <tr>
        <td>CCC: {$ccc}</td>
        <td>CLIENTE: {$company}</td>
        <td></td>
        <td>DIRECCIÓN: {$street_type} {$street_name} N {$street_number}</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>POBLACION: {$city}</td>
        <td>C.P.: {$postalcode}</td>
        <td>PROVINCIA: {$state}</td>
        <td>CIF/NIF: {$cif}</td>
    </tr>
    <tr>
        <td>TOTAL EXP: {$total_expedidiciones}</td>
        <td>TOTAL BULTOS: {$total_bultos}</td>
        <td>TOTAL KG. : {$total_kgs}</td>
        <td>TOTAL REEMBOLSO : {$total_reembolso} &euro;</td>
        <td></td>
    </tr>
</table>
<br>
<br>

{foreach $orders as $order}

<table style="padding:2px ">
    <tr>
        <td style="text-decoration:underline">REFERENCIA</td>
        <td style="text-decoration:underline">CONSIGNATARIO</td>
        <td style="text-decoration:underline">PRO</td>
        <td style="text-decoration:underline">SER</td>
        <td style="text-decoration:underline">BUL</td>
        <td style="text-decoration:underline">KGS</td>
        <td style="text-decoration:underline">POR</td>
        <td style="text-decoration:underline">REEMBOLSO</td>
        <td style="text-decoration:underline">SEGURO</td>
        <td style="text-decoration:underline">CDE</td>
    </tr>
</table>
<br>
<table style="padding:2px; border:2px solid #000;">
    <tr>
        <td>{$order.reference}</td>
        <td>{$order.consig_name}</td>
        <td>{$order.producto}</td>
        <td>{$order.servicio}</td>
        <td>{$order.bultos}</td>
        <td>{$order.peso}</td>
        <td>F</td>
        <td>{$order.cashondelivery+0}</td>
        <td>0.0</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td>{$order.consig_address}</td>
        <td></td>
        <td>N 0000,</td>
        <td></td>
        <td>TLF: {$order.consig_phone}</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td colspan="2">C.P.:{$order.consig_postalcode}</td>
        <td>{$order.state|upper}</td>
        <td colspan="2">PAIS DESTINO {$order.country|upper}</td>
        <td colspan="2">ATT:</td>
        <td colspan="2">OBS: {$order.otros}</td>
    </tr>
    <tr>
        <td colspan="10">Datos de consignatario: {$order.consig_name}</td>
    </tr>
    <tr >
        <td style="border-top:1px solid #000;border-bottom:1px solid #000;">ECB</td>
        <td style="border-top:1px solid #000;border-bottom:1px solid #000;">REF. BULTO</td>
        <td colspan="2" style="border-top:1px solid #000;border-bottom:1px solid #000;">OBSERVACIONES</td>
        <td style="border-top:1px solid #000;border-bottom:1px solid #000;">VALOR RS</td>
        <td style="border-top:1px solid #000;border-bottom:1px solid #000;">PESO</td>
        <td style="border-top:1px solid #000;border-bottom:1px solid #000;">ALTO</td>
        <td style="border-top:1px solid #000;border-bottom:1px solid #000;">ANCHO</td>
        <td style="border-top:1px solid #000;border-bottom:1px solid #000;">LARGO</td>
        <td style="border-top:1px solid #000;border-bottom:1px solid #000;">VOLUMEN</td>
    </tr>
    {assign var="ref" value=1}
    {foreach from=$order.ecb  item=ecb}
        <tr>
            <td>{$ecb}</td>
            <td>{$order.id|str_pad:3:"0":$smarty.const.STR_PAD_LEFT}{$ref|str_pad:3:"0":$smarty.const.STR_PAD_LEFT}</td>
            <td colspan="2">{$order.otros}</td>
            <td>0.0</td>
            <td>{$order.peso}</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
        </tr>
        {$ref = $ref+1}
    {/foreach}
</table>
<br>
<br>
{/foreach}

<br><br>
<table style="padding:2px; border:1px solid #000;">
    <tr>
        <td colspan="2" style="border-bottom:1px solid #000;border-right:1px solid #000;">INFORME DE DETALLE DE ENVIOS Y BULTOS de {date('d/m/Y')}</td>
        <td style="border-bottom:1px solid #000;">TOTAL GENERAL </td>
    </tr>
    <tr>
        <td colspan="2" style="border-bottom:1px solid #000;border-right:1px solid #000;">CONFORME</td>
        <td style="border-bottom:1px solid #000;">TOTAL EXPEDICIONES <span style="float:right">{$total_expedidiciones}</span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #000;border-right:1px solid #000;">CLIENTE</td>
        <td style="border-bottom:1px solid #000;border-right:1px solid #000;">CONDUCTOR</td>
        <td style="border-bottom:1px solid #000;">TOTAL BULTOS <span style="float:right">{$total_bultos}</span></td>
    </tr>
    <tr>
        <td rowspan="5" style="border-bottom:1px solid #000;border-right:1px solid #000;"></td>
        <td rowspan="5" style="border-bottom:1px solid #000;border-right:1px solid #000;"></td>
        <td style="border-bottom:1px solid #000;">TOTAL KGS: <span style="float:right">{$total_kgs}</span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #000;">TOTAL REEMBOLSO: <span style="float:right">{$total_reembolso} &euro;</span></td>
    </tr>
    <tr>
        <td style="border-bottom:1px solid #000;">TOTAL SEGURO: <span style="float:right">{$total_seguro} &euro;</span></td>
    </tr>
    <tr>
        <td>TOTAL VALOR RS: <span style="float:right">{$total_valor_rs} &euro;</span></td>
    </tr>
</table>
<br>
<span style="font-size: 8px">SEUR no reconoce el valor y contenido de la mercancia transportada, que se entrega cerrada y embalada, la responsabilidad de SEUR es la fijada en su carta de porte.</span>
<br>
<br>
<span style="font-size: 8px">ONSERVACIONES</span>
<br>
<hr>
