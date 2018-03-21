(function(){

    var estatuscasosrvc = angular.module('cpm.estatuscasosrvc', ['cpm.comunsrvc']);

    estatuscasosrvc.factory('estatusCasoSrvc', ['comunFact', function(comunFact){
        var urlBase = 'php/estatuscaso.php';

        return {
            lstEstatus: function(){
                return comunFact.doGET(urlBase + '/lstestatus');
            },
            getEstatus: function(idestatus){
                return comunFact.doGET(urlBase + '/getestatus/' + idestatus);
            },
            editRow: function(obj, op){
                return comunFact.doPOST(urlBase + '/' + op, obj);
            }
        };
    }]);

}());

