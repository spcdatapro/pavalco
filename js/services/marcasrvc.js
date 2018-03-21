(function(){

    var marcasrvc = angular.module('cpm.marcasrvc', ['cpm.comunsrvc']);

    marcasrvc.factory('marcaSrvc', ['comunFact', function(comunFact){
        var urlBase = 'php/marca.php';

        return {
            lstMarcas: function(){
                return comunFact.doGET(urlBase + '/lstmarcas');
            },
            getMarca: function(idmarca){
                return comunFact.doGET(urlBase + '/getmarca/' + idmarca);
            },
            editRow: function(obj, op){
                return comunFact.doPOST(urlBase + '/' + op, obj);
            }
        };
    }]);

}());

