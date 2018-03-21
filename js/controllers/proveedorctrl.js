(function(){

    var proveedorctrl = angular.module('cpm.proveedorctrl', ['cpm.proveedorsrvc']);

    proveedorctrl.controller('proveedorCtrl', ['$scope', 'proveedorSrvc', 'DTOptionsBuilder', 'empresaSrvc', 'cuentacSrvc', 'authSrvc', '$confirm', 'monedaSrvc', '$filter', '$route', function($scope, proveedorSrvc, DTOptionsBuilder, empresaSrvc, cuentacSrvc, authSrvc, $confirm, monedaSrvc, $filter, $route){
        //$scope.tituloPagina = 'CPM';

        $scope.elProv = {};
        $scope.losProvs = [];
        $scope.editando = false;
        $scope.strProveedor = '';
        $scope.elDetContProv = {};
        $scope.detContProv = [];
        $scope.lasEmpresas = [];
        $scope.objEmpresa = {};
        $scope.lasCuentas = [];
        $scope.monedas = [];
        $scope.dectc = 2;
        $scope.permiso = {};
        $scope.conconta = false;

        $scope.dtOptions = DTOptionsBuilder.newOptions().withPaginationType('full_numbers').withBootstrap().withOption('responsive', true);

        empresaSrvc.lstEmpresas().then(function(d){ $scope.lasEmpresas = d; });

        authSrvc.getSession().then(function(usrLogged){
            if(parseInt(usrLogged.workingon) > 0){
                authSrvc.gpr({idusuario: parseInt(usrLogged.uid), ruta:$route.current.params.name}).then(function(d){ $scope.permiso = d; });
                empresaSrvc.getEmpresa(parseInt(usrLogged.workingon)).then(function(r){
                    $scope.objEmpresa = r[0];
                    $scope.dectc = parseInt($scope.objEmpresa.dectc);
                    monedaSrvc.lstMonedas().then(function(d){
                        $scope.monedas = d;
                        $scope.resetElProv();
                    });
                    empresaSrvc.conConta($scope.objEmpresa.id).then(function(d){ $scope.conconta = d; });
                });
            }
        });

        $scope.$watch('objEmpresa', function(newValue, oldValue){
            if(newValue != null && newValue != undefined){
                $scope.getLstCuentas();
            }
        });

        $scope.resetElProv = function(){
            $scope.elProv = { direccion: '', telefono: '', correo: '', concepto: '', chequesa: '', retensionisr: 0, diascred: 0,
                limitecred: parseFloat(0.0), pequeniocont: 0, tipocambioprov: 1, objMoneda: {} };
            $scope.editando = false;
            $scope.strProveedor = '';
            monedaSrvc.getMoneda(parseInt($scope.objEmpresa.idmoneda)).then(function(d){
                $scope.elProv.objMoneda = d[0];
                $scope.elProv.objMoneda.tipocambioprov = parseFloat($scope.elProv.objMoneda.tipocambioprov).toFixed($scope.dectc);
                $scope.elProv.tipocambioprov = parseFloat(d[0].tipocambio).toFixed($scope.dectc);
            });
        };

        $scope.getLstProveedores = function(){
            proveedorSrvc.lstProveedores().then(function(d){
                $scope.losProvs = d;
                for(var i = 0; i < $scope.losProvs.length; i++){
                    $scope.losProvs[i].retensionisr = parseInt($scope.losProvs[i].retensionisr);
                    $scope.losProvs[i].diascred = parseInt($scope.losProvs[i].diascred);
                    $scope.losProvs[i].limitecred = parseFloat($scope.losProvs[i].limitecred);
                    $scope.losProvs[i].pequeniocont = parseInt($scope.losProvs[i].pequeniocont);
                }
            });
        };

        function procData(data){
            data.id = parseInt(data.id);
            data.retensionisr = parseInt(data.retensionisr);
            data.diascred = parseInt(data.diascred);
            data.limitecred = parseFloat(data.limitecred);
            data.pequeniocont = parseInt(data.pequeniocont);
            data.idmoneda = parseInt(data.idmoneda);
            data.tipocambioprov = parseFloat(data.tipocambioprov).toFixed($scope.dectc);
            return data;
        };

        $scope.getLstDetCuentaC = function(idprov){
            proveedorSrvc.lstDetCuentaC(parseInt(idprov)).then(function(det){
                $scope.detContProv = det;
                goTop();
            });
        };

        $scope.getDataProv = function(idprov){
            proveedorSrvc.getProveedor(parseInt(idprov)).then(function(d){
                var provTmp = procData(d[0]);
                $scope.elProv.id = provTmp.id;
                $scope.elProv.nit = provTmp.nit;
                $scope.elProv.nombre = provTmp.nombre;
                $scope.elProv.direccion = provTmp.direccion;
                $scope.elProv.telefono = provTmp.telefono;
                $scope.elProv.correo = provTmp.correo;
                $scope.elProv.concepto = provTmp.concepto;
                $scope.elProv.chequesa = provTmp.chequesa;
                $scope.elProv.retensionisr = provTmp.retensionisr;
                $scope.elProv.diascred = provTmp.diascred;
                $scope.elProv.limitecred = provTmp.limitecred;
                $scope.elProv.pequeniocont = provTmp.pequeniocont;
                $scope.elProv.idmoneda = provTmp.idmoneda;
                $scope.elProv.tipocambioprov = provTmp.tipocambioprov;
                $scope.elProv.objMoneda = $filter('getById')($scope.monedas, $scope.elProv.idmoneda);
                $scope.strProveedor = 'No. ' + pad($scope.elProv.id, 4) + ', ' + provTmp.nitnombre;
                $scope.editando = true;
                $scope.getLstDetCuentaC(idprov);
            });
        };

        $scope.addProv = function(obj){
            obj.idmoneda = parseInt(obj.objMoneda.id);
            proveedorSrvc.editRow(obj, 'c').then(function(){
                $scope.getLstProveedores();
                $scope.resetElProv();
            });
        };

        $scope.updProv = function(data, id){
            data.idmoneda = parseInt(data.objMoneda.id);
            proveedorSrvc.editRow(data, 'u').then(function(){
                $scope.getLstProveedores();
                $scope.resetElProv();
                $scope.strProveedor = '';
                $scope.editando = false;
            });
        };

        $scope.delProv = function(id){
            $confirm({
                text: "¿Seguro(a) de eliminar este proveedor? (Esto también eliminara el detalle contable.)",
                title: 'Eliminar proveedor',
                ok: 'Sí',
                cancel: 'No'})
                .then(function() {
                    //console.log(id);
                    proveedorSrvc.editRow({id:id}, 'd').then(function(){
                        $scope.resetElProv();
                        $scope.getLstProveedores();
                    });
                });
        };

        $scope.getLstProveedores();

        $scope.getLstCuentas = function(){
            if($scope.objEmpresa.id !== null && $scope.objEmpresa.id !== undefined){
                cuentacSrvc.getByTipo(parseInt($scope.objEmpresa.id), 0).then(function(d){
                    $scope.lasCuentas = d;
                });
            }
        };

        $scope.addDetProv = function(obj){
            obj.idproveedor = $scope.elProv.id;
            obj.idcuentac = parseInt($scope.elDetContProv.objCuentaC[0].id);
            proveedorSrvc.editRow(obj, 'cd').then(function(){
                $scope.getLstDetCuentaC($scope.elProv.id);
                $scope.elDetContProv = {};
                $scope.searchcta = "";
            });
        };

        $scope.delDetProv = function(iddetprov){
            $confirm({text: '¿Seguro(a) de eliminar esta cuenta?', title: 'Eliminar cuenta contable', ok: 'Sí', cancel: 'No'}).then(function() {
                proveedorSrvc.editRow({id:iddetprov}, 'dd').then(function(){ $scope.getLstDetCuentaC($scope.elProv.id); });
            });
        };

    }]);

}());
