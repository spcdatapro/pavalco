(function(){

    var equiposrvc = angular.module('cpm.equiposrvc', ['cpm.comunsrvc']);

    equiposrvc.factory('equipoSrvc', ['comunFact', function(comunFact){
        var urlBase = 'php/equipo.php';

        return {
            lstEquipos: function(){
                return comunFact.doGET(urlBase + '/lstequipos');
            },
            getEquipo: function(idequipo){
                return comunFact.doGET(urlBase + '/getequipo/' + idequipo);
            },
            editRow: function(obj, op){
                return comunFact.doPOST(urlBase + '/' + op, obj);
            }
        };
    }]);

}());