(function(){

    var cuentacctrl = angular.module('cpm.cuentacctrl', ['cpm.cuentacsrvc']);

    cuentacctrl.controller('cuentacCtrl', ['$scope', 'authSrvc', 'cuentacSrvc', 'empresaSrvc', 'DTOptionsBuilder', '$confirm', function($scope, authSrvc, cuentacSrvc, empresaSrvc, DTOptionsBuilder, $confirm){
        //$scope.tituloPagina = 'CPM';

        $scope.laCta = {};
        $scope.lasEmpresas = [];
        $scope.lasCuentas = [];
        $scope.editando = false;

        $scope.dtOptions = DTOptionsBuilder.newOptions().withPaginationType('full_numbers').withBootstrap().withOption('responsive', true).withOption('ordering', false);

        empresaSrvc.lstEmpresas().then(function(d){
            $scope.lasEmpresas = d;
        });

        authSrvc.getSession().then(function(usrLogged){
            if(parseInt(usrLogged.workingon) > 0){
                empresaSrvc.getEmpresa(parseInt(usrLogged.workingon)).then(function(r){
                    $scope.laCta.objEmpresa = r[0];
                });
            }
        });

        $scope.resetLaCta = function(){
            $scope.laCta = {objEmpresa:$scope.laCta.objEmpresa, idempresa:$scope.laCta.objEmpresa.id, codigo: '', nombrecta: '', tipocuenta: 0, integracliente: 0};
            $scope.editando = false;
        };

        $scope.$watch('laCta.objEmpresa', function(newValue, oldValue){
            if(newValue != null && newValue != undefined){
                $scope.getLstCuentas();
            }
        });

        function procAllData(data){
            for(var i = 0; i < data.length; i++){
                data[i].tipocuenta = parseInt(data[i].tipocuenta);
                data[i].integracliente = parseInt(data[i].integracliente);
            }
            return data;
        }

        $scope.getLstCuentas = function(){
            cuentacSrvc.lstCuentasC($scope.laCta.objEmpresa.id).then(function(d){
                $scope.lasCuentas = procAllData(d);
            });
        };

        function procData(data){
            data.id = parseInt(data.id);
            data.idempresa = parseInt(data.idempresa);
            data.tipocuenta = parseInt(data.tipocuenta);
            data.integracliente = parseInt(data.integracliente);
            data.objEmpresa = $scope.laCta.objEmpresa;
            return data;
        }

        $scope.getDataCta = function(idcta){
            cuentacSrvc.getCuentaC(parseInt(idcta)).then(function(d){
                $scope.laCta = procData(d[0]);
                $scope.editando = true;
                goTop();
            });
        };

        $scope.addCuenta = function(obj){
            obj.idempresa = $scope.laCta.objEmpresa.id;
            obj.tipocuenta = obj.tipocuenta == null || obj.tipocuenta == undefined ? 0 : parseInt(obj.tipocuenta);
            obj.integracliente = obj.integracliente == null || obj.integracliente == undefined ? 0 : parseInt(obj.integracliente);
            cuentacSrvc.editRow(obj, 'c').then(function(){
                $scope.getLstCuentas();
                $scope.laCta = {objEmpresa:$scope.laCta.objEmpresa, idempresa:$scope.laCta.objEmpresa.id, codigo: '', nombrecta: '', tipocuenta: 0};
            });
        };

        $scope.updCuenta = function(data, id){
            data.id = id;
            data.idempresa = $scope.laCta.objEmpresa.id;
            data.tipocuenta = data.tipocuenta == null || data.tipocuenta == undefined ? 0 : parseInt(data.tipocuenta);
            data.integracliente = data.integracliente == null || data.integracliente == undefined ? 0 : parseInt(data.integracliente);
            cuentacSrvc.editRow(data, 'u').then(function(){
                $scope.getLstCuentas();
                $scope.resetLaCta();
            });
        };

        $scope.delCuenta = function(id){
            $confirm({text: '¿Seguro(a) de eliminar esta cuenta?', title: 'Eliminar cuenta contable', ok: 'Sí', cancel: 'No'}).then(function() {
                cuentacSrvc.editRow({id:id}, 'd').then(function(){ $scope.getLstCuentas(); });
            });
        };

    }]);

}());
