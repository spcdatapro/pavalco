(function(){

    var ubicacionsrvc = angular.module('cpm.ubicacionsrvc', ['cpm.comunsrvc']);

    ubicacionsrvc.factory('ubicacionSrvc', ['comunFact', function(comunFact){
        var urlBase = 'php/ubicacion.php';

        return {
            lstUbicaciones: function(){
                return comunFact.doGET(urlBase + '/lstubicaciones');
            },
            getUbicacion: function(idubicacion){
                return comunFact.doGET(urlBase + '/getubicacion/' + idubicacion);
            },
            editRow: function(obj, op){
                return comunFact.doPOST(urlBase + '/' + op, obj);
            }
        };
    }]);

}());
