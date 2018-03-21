(function(){

    var clientesrvc = angular.module('cpm.clientesrvc', ['cpm.comunsrvc']);

    clientesrvc.factory('clienteSrvc', ['comunFact', function(comunFact){
        var urlBase = 'php/cliente.php';

        var clienteSrvc = {
            lstAllClientes: function(){
                return comunFact.doGET(urlBase + '/lstallclientes');
            },
            lstClientesByPromotor: function(idpromotor){
                return comunFact.doGET(urlBase + '/lstclientesbypromotor/' + idpromotor);
            },
            getCliente: function(idcliente){
                return comunFact.doGET(urlBase + '/getcliente/' + idcliente);
            },
            editRow: function(obj, op){
                return comunFact.doPOST(urlBase + '/' + op, obj);
            }
        };
        return clienteSrvc;
    }]);

}());
