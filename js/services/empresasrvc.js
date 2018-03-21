(function(){

    var empresasrvc = angular.module('cpm.empresasrvc', ['cpm.comunsrvc']);

    empresasrvc.factory('empresaSrvc', ['comunFact', function(comunFact){
        var urlBase = 'php/empresa.php';

        return {
            lstEmpresas: function(){
                return comunFact.doGET(urlBase + '/lstempresas');
            },
            getEmpresa: function(idempresa){
                return comunFact.doGET(urlBase + '/getemp/' + idempresa);
            },
            lstConfigConta: function(idempresa){
                return comunFact.doGET(urlBase + '/lstconf/' + idempresa);
            },
            getConfConta: function(idconf){
                return comunFact.doGET(urlBase + '/getconf/' + idconf);
            },
            getDecimalesTC: function(idempresa){
                return comunFact.doGET(urlBase + '/getdectc/' + idempresa);
            },
            conConta: function(idempresa){
                return comunFact.doGET(urlBase + '/conconta/' + idempresa).then(function(d){
                    return parseInt(d.conconta) != 0;
                });
            },
            porDefecto: function(){
                return comunFact.doGET(urlBase + '/pordefecto');
            },
            editRow: function(obj, op){
                return comunFact.doPOST(urlBase + '/' + op, obj);
            }
        };
    }]);

}());
