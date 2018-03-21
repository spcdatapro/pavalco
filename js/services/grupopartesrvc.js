(function(){

    var grupopartesrvc = angular.module('cpm.grupopartesrvc', ['cpm.comunsrvc']);

    grupopartesrvc.factory('grupoParteSrvc', ['comunFact', function(comunFact){
        var urlBase = 'php/grupoparte.php';

        return {
            lstGruposPartes: function(){
                return comunFact.doGET(urlBase + '/lstgruposparte');
            },
            getGrupoParte: function(idgrupoparte){
                return comunFact.doGET(urlBase + '/getgrupoparte/' + idgrupoparte);
            },
            lstAllSubGruposPartes: function(){
                return comunFact.doGET(urlBase + '/lstallsubgrupospartes');
            },
            lstSubGruposPartesByGrupo: function(idgrupoparte){
                return comunFact.doGET(urlBase + '/lstgrupospartebygrupo/' + idgrupoparte);
            },
            getSubGrupoParte: function(idsubgrupoparte){
                return comunFact.doGET(urlBase + '/getsubgrupoparte/' + idsubgrupoparte);
            },
            editRow: function(obj, op){
                return comunFact.doPOST(urlBase + '/' + op, obj);
            }
        };
    }]);

}());