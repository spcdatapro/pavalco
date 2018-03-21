(function(){

    var proveedorsrvc = angular.module('cpm.proveedorsrvc', ['cpm.comunsrvc']);

    proveedorsrvc.factory('proveedorSrvc', ['comunFact', function(comunFact){
        var urlBase = 'php/proveedor.php';

        var proveedorSrvc = {
            lstProveedores: function(){
                return comunFact.doGET(urlBase + '/lstprovs');
            },
            getProveedor: function(idprov){
                return comunFact.doGET(urlBase + '/getprov/' + idprov);
            },
            editRow: function(obj, op){
                return comunFact.doPOST(urlBase + '/' + op, obj);
            },
            lstDetCuentaC: function(idprov){
                return comunFact.doGET(urlBase + '/detcontprov/' + idprov);
            },
            getDetCuentaC: function(iddet){
                return comunFact.doGET(urlBase + '/getdetcontprov/' + iddet);
            },
            getLstCuentasCont: function(idprov){
                return comunFact.doGET(urlBase + '/lstdetcontprov/' + idprov);
            }
        };
        return proveedorSrvc;
    }]);

}());
