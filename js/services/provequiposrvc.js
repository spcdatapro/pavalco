(function(){

    var provequiposrvc = angular.module('cpm.provequiposrvc', ['cpm.comunsrvc']);

    provequiposrvc.factory('provEquipoSrvc', ['comunFact', function(comunFact){
        var urlBase = 'php/provequipo.php';

        var provEquipoSrvc = {
            lstProvsEq: function(){
                return comunFact.doGET(urlBase + '/lstprovseq');
            },
            getProvEq: function(idproveq){
                return comunFact.doGET(urlBase + '/getproveq/' + idproveq);
            },
            editRow: function(obj, op){
                return comunFact.doPOST(urlBase + '/' + op, obj);
            }
        };
        return provEquipoSrvc;
    }]);

}());
