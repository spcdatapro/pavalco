(function(){

    var razonanulacionsrvc = angular.module('cpm.razonanulacionsrvc', ['cpm.comunsrvc']);

    razonanulacionsrvc.factory('razonAnulacionSrvc', ['comunFact', function(comunFact){
        var urlBase = 'php/razonanulacion.php';

        var razonAnulacionSrvc = {
            lstRazones: function(){
                return comunFact.doGET(urlBase + '/lstrazones');
            },
            getRazon: function(idrazon){
                return comunFact.doGET(urlBase + '/getrazon/' + idrazon);
            },
            editRow: function(obj, op){
                return comunFact.doPOST(urlBase + '/' + op, obj);
            }
        };
        return razonAnulacionSrvc;
    }]);

}());
