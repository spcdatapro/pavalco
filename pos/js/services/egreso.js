angular.module('cpm')
.factory('ventaServicios', ['comunFact', function(comunFact){
    var urlBase = 'pos/php/controllers/egreso.php';

    return {
        buscar: function(obj){
            return comunFact.doGETJ(urlBase + '/buscar', obj);
        },
        buscarProducto: function(obj){
            return comunFact.doGETJ(urlBase + '/buscar_producto', obj);
        }, 
        buscarNit: function(nit) {
        	return comunFact.doGET(urlBase + '/buscar_nit/'+nit);
        },
        getBodegas: function() {
            return comunFact.doGET(urlBase + '/get_bodegas');
        }, 
        iniciarVenta: function(bod) {
            return comunFact.doPOSTJ(urlBase + '/iniciar_venta', bod);
        }, 
        agregarDetalle: function(venta, item) {
            return comunFact.doPOSTJ(urlBase + '/agregar_detalle/'+venta, item);
        },
        eliminarDetalle: function(venta, item) {
            return comunFact.doPOSTJ(urlBase + '/eliminar_detalle/'+venta, item);
        },
        verDetalle: function(venta) {
            return comunFact.doGET(urlBase + '/ver_detalle/'+venta);
        }, 
        getMetodoPago: function() {
            return comunFact.doGET(urlBase + '/get_metodopago');
        }
    };
}]);