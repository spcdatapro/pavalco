(function(){

    var contratorvc = angular.module('cpm.contratorvc', ['cpm.comunsrvc']);

    contratorvc.factory('contratoSrvc', ['comunFact', function(comunFact){
        var urlBase = 'php/contrato.php';

        return {
            lstContratos: function(idempresa){
                return comunFact.doGET(urlBase + '/lstcontratos/' + idempresa);
            },
            lstContratosByEmpByCli: function(idempresa, idcliente){
                return comunFact.doGET(urlBase + '/lstcontbyempcli/' + idempresa + '/' + idcliente);
            },
            getContrato: function(idcontrato){
                return comunFact.doGET(urlBase + '/getcontrato/' + idcontrato);
            },
            editRow: function(obj, op){
                return comunFact.doPOST(urlBase + '/' + op, obj);
            },
            lstDetProvEq: function(idcontrato){
                return comunFact.doGET(urlBase + '/lstdetproveq/' + idcontrato);
            },
            getDetProvEq: function(iddetproveq){
                return comunFact.doGET(urlBase + '/getdetproveq/' + iddetproveq);
            },
            lstDetDatosEsp: function(idcontrato){
                return comunFact.doGET(urlBase + '/lstdetesp/' + idcontrato);
            },
            getDetDatosEsp: function(iddetdatosesp){
                return comunFact.doGET(urlBase + '/getdetesp/' + iddetdatosesp);
            },
            getDatosFact: function(idcontrato){
                return comunFact.doGET(urlBase + '/getfactdata/' + idcontrato);
            },
            chkForCargos: function(idcontrato){
                return comunFact.doGET(urlBase + '/chkforcargos/' + idcontrato);
            },
            genCargos: function(obj){
                return comunFact.doPOST(urlBase + '/gencargos', obj);
            },
            autorizar: function(obj){
                return comunFact.doPOST(urlBase + '/autorizar', obj);
            },
            lstConfigCargos: function(idcontrato){
                return comunFact.doGET(urlBase + '/lstconfcargos/' + idcontrato);
            },
            getConfigCargos: function(idconfcargo){
                return comunFact.doGET(urlBase + '/getconfcargos/' + idconfcargo);
            },
            lstDetConfigCargos: function(idconfcargo){
                return comunFact.doGET(urlBase + '/lstdetconfcargo/' + idconfcargo);
            },
            getDetConfigCobros: function(iddetconfcargo){
                return comunFact.doGET(urlBase + '/getdetconfcargo/' + iddetconfcargo);
            },
            lstCargos: function(idcontrato){
                return comunFact.doGET(urlBase + '/lstcargos/' + idcontrato);
            }

        };
    }]);

}());
