(function(){

    var rptconttecfuentesrvc = angular.module('cpm.rptconttecfuentesrvc', ['cpm.comunsrvc']);

    rptconttecfuentesrvc.factory('rptConteoTecnicoFuenteSrvc', ['comunFact', function(comunFact){
        var urlBase = 'php/rptconttecfuente.php';

        return {
            rptConteoTecnicoFuente: function(obj){
                return comunFact.doPOST(urlBase + '/resumen', obj);
            }
        };

    }]);

}());