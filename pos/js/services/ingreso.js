angular.module('cpm')
.factory('ingresoServicios', ['comunFact', function(comunFact){
    var urlBase = 'pos/php/controllers/ingreso.php';

    return {
        buscar: function(obj){
            return comunFact.doGETJ(urlBase + '/buscar', obj);
        },
        guardar: function(obj){
            return comunFact.doPOSTJ(urlBase + '/guardar', obj);
        }, 
        agregarDetalle: function(ingreso, item) {
            return comunFact.doPOSTJ(urlBase + '/agregar_detalle/'+ingreso, item);
        },
        eliminarDetalle: function(ingreso, item) {
            return comunFact.doPOSTJ(urlBase + '/eliminar_detalle/'+ingreso, item);
        },
        verDetalle: function(ingreso) {
            return comunFact.doGET(urlBase + '/ver_detalle/'+ingreso);
        }
    };
}]);