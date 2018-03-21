(function(){

    var detcontsrvc = angular.module('cpm.detcontsrvc', ['cpm.comunsrvc']);

    detcontsrvc.factory('detContSrvc', ['comunFact', function(comunFact){
        var urlBase = 'php/detallecontable.php';

        return {
            lstDetalleCont: function(origen, idorigen){
                return comunFact.doGET(urlBase + '/lstdetcont/' + origen + '/' + idorigen);
            },
            getDetalleCont: function(iddetcont){
                return comunFact.doGET(urlBase + '/getdetcont/' + iddetcont);
            },
            estaCuadrada: function(origen, idorigen){
                return comunFact.doGET(urlBase + '/cuadrada/' + origen + '/' + idorigen);
            },
            editRow: function(obj, op){
                return comunFact.doPOST(urlBase + '/' + op, obj);
            },
            rptDetContFact: function(obj){
                return comunFact.doPOST(urlBase + '/rptdetcontfact', obj);
            },
            rptDetContDocs: function (obj) {
                return comunFact.doPOST(urlBase + '/rptdetcontdocs', obj);
            }
        };
    }]);

}());
