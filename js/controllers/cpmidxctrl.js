(function(){

    var cpmidxctrl = angular.module('cpm.cpmidxctrl', ['cpm.authsrvc', 'toaster']);

    cpmidxctrl.controller('cpmIdxCtrl', ['$scope', '$rootScope', '$uibModal', '$window', 'authSrvc', 'toaster', 'empresaSrvc', function($scope, $rootScope, $uibModal, $window, authSrvc, toaster, empresaSrvc){
        $scope.tituloPagina = 'CPM - Bienvenido';

        $scope.menuUsr = [];
        $scope.qEmpresa = {};
        $scope.lasEmpresas = [];

        empresaSrvc.lstEmpresas().then(function(d){
            $scope.lasEmpresas = d;
        });

        authSrvc.getSession().then(function(usrLogged){
            authSrvc.getMenu(parseInt(usrLogged.uid)).then(function(res){
                $scope.menuUsr = res;
                if(parseInt(usrLogged.workingon) === 0){
                    var porDefecto = 0;
                    empresaSrvc.porDefecto().then(function(d){
                        porDefecto = d.pordefecto;
                        if(porDefecto > 0){
                            empresaSrvc.getEmpresa(porDefecto).then(function(r){
                                $scope.qEmpresa = r[0];
                                authSrvc.setEmpresaSess(r[0].id).then(function(s){ $rootScope.workingon = parseInt(s.workingon); });
                            });
                        }else{
                            $scope.openSetEmpresa();
                        }
                    });
                }else{
                    empresaSrvc.getEmpresa(parseInt(usrLogged.workingon)).then(function(r){
                        $scope.qEmpresa = r[0];
                    });
                }
            });
        });

        $scope.$watch('qEmpresa', function(newValue, oldValue) {
            var oldEmp = oldValue.nomempresa != null && oldValue.nomempresa != undefined ? oldValue.nomempresa : 'ninguna';
            var msg = 'Cambió la empresa que está trabajando de '+ oldEmp + ' a ' + newValue.nomempresa;
            toaster.pop('info', 'Cambio de empresa de trabajo', msg);
        });

        $scope.openSetEmpresa = function(){
            var modalInstance = $uibModal.open({
                animation: true,
                templateUrl: 'modalSelectEmpresa.html',
                controller: 'ModalInstanceCtrl',
                resolve:{
                    empresas: function(){ return $scope.lasEmpresas; }
                }
            });

            modalInstance.result.then(function(selectedItem){
                $scope.qEmpresa = selectedItem;
                authSrvc.setEmpresaSess(selectedItem.id).then(function(r){
                    $rootScope.workingon = parseInt(r.workingon);
                });
            }, function(){
                toaster.pop('warning', 'Cambio de empresa de trabajo', 'Canceló el cambio de empresa...');
            });
        };

        $scope.doLogOut = function(){
            authSrvc.doLogOut().then(function(res){
                $rootScope.logged = false;
                $rootScope.uid = 0;
                $rootScope.fullname = null;
                $rootScope.usuario = null;
                $rootScope.correoe = null;
                $rootScope.workingon = 0;
                $window.location.href = 'index.html';
            });
        };

    }]);

    cpmidxctrl.controller('ModalInstanceCtrl', ['$scope', '$rootScope', '$uibModalInstance', 'empresas', function($scope, $rootScope, $uibModalInstance, empresas){
        $scope.lasEmpresas = empresas;
        $scope.objEmpresa = {};

        $scope.seleccionada = true;

        $scope.yaSelecciono = function(){

            if($rootScope.workingon)

            $scope.seleccionada = !($scope.objEmpresa.id != null && $scope.objEmpresa.id != undefined);
        };

        $scope.ok = function () {
            $uibModalInstance.close($scope.objEmpresa);
        };

        $scope.cancel = function () {
            $uibModalInstance.dismiss('cancel');
        };

    }]);

}());
