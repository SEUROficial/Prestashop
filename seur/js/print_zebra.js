
//[CFL] 240614 - Eliminada la etiqueta <PRE> ya que para Geolabel no imprimia la etiqueta. Reemplazada con <div>
//[CFL] 240614 - document.write(" <pre  style='color: #FFFFFF;'>	");

//cargamos la variable js zebra con el contenido importado
document.write("<pre>");
document.write(zebra);
document.write("</pre>");
window.print();

/*var content = document.getElementById("textoTotalDatosImpresionTermica");
var pri = document.getElementById("ifmcontentstoprint").contentWindow;
pri.document.open();
pri.document.write(content.value);
pri.document.close();
pri.focus();
pri.print();*/

var int=self.setInterval("cerrar()",50);

function cerrar(){
    int=window.clearInterval(int);
    window.close();
}
