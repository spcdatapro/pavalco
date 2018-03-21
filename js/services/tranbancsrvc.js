(function(){

    var tranbacsrvc = angular.module('cpm.tranbacsrvc', ['cpm.comunsrvc']);

    tranbacsrvc.factory('tranBancSrvc', ['comunFact', function(comunFact){
        var urlBase = 'php/tranbanc.php';

        return {
            lstTransacciones: function(idbanco){
                return comunFact.doGET(urlBase + '/lsttranbanc/' + idbanco);
            },
            getTransaccion: function(idtran){
                return comunFact.doGET(urlBase + '/gettran/' + idtran);
            },
            lstBeneficiarios: function(){
                return comunFact.doGET(urlBase + '/lstbeneficiarios');
            },
            lstFactCompra: function(idproveedor, idtranban){
                return comunFact.doGET(urlBase + '/factcomp/' + idproveedor + '/' + idtranban);
            },
            lstReembolsos: function(idbene){
                return comunFact.doGET(urlBase + '/reem/' + idbene);
            },
            editRow: function(obj, op){
                return comunFact.doPOST(urlBase + '/' + op, obj);
            },
            lstDocsSoporte: function(idtran){
                return comunFact.doGET(urlBase + '/lstdocsop/' + idtran);
            },
            getDocSoporte: function(iddocsop){
                return comunFact.doGET(urlBase + '/getdocsop/' + iddocsop);
            },
            getSumDocsSop: function(idtran){
                return comunFact.doGET(urlBase + '/getsumdocssop/' + idtran);
            },
            rptcorrch: function(obj){
                return comunFact.doPOST(urlBase + '/rptcorrch', obj);
            },
            rptdocscircula: function(obj){
                return comunFact.doPOST(urlBase + '/rptdocscircula', obj);
            },
            lstAConciliar: function(idbanco, afecha){
                return comunFact.doGET(urlBase + '/aconciliar/' + idbanco + '/' + afecha);
            }
        };
    }]);

}());
