(function(){

    var tecnicosrvc = angular.module('cpm.tecnicosrvc', ['cpm.comunsrvc']);

    tecnicosrvc.factory('tecnicoSrvc', ['comunFact', function(comunFact){
        var urlBase = 'php/tecnico.php';

        return {
            lstTecnicos: function(){
                return comunFact.doGET(urlBase + '/lsttecnicos');
            },
            getTecnico: function(idtecnico){
                return comunFact.doGET(urlBase + '/gettecnico/' + idtecnico);
            },
            editRow: function(obj, op){
                return comunFact.doPOST(urlBase + '/' + op, obj);
            }
        };
    }]);

}());
