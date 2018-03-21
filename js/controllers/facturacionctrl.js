(function(){

    var facturacionctrl = angular.module('cpm.facturacionctrl', []);

    facturacionctrl.controller('facturacionCtrl', ['$scope', 'facturacionSrvc', 'authSrvc', 'empresaSrvc', '$confirm', 'toaster', '$uibModal', 'tipoCambioSrvc', function($scope, facturacionSrvc, authSrvc, empresaSrvc, $confirm, toaster, $uibModal, tipoCambioSrvc){

        $scope.laEmpresa = {};
        $scope.lasEmpresas = [];
        $scope.cobros = [];
        $scope.params = {fal: moment(moment().add(1, 'M')).endOf('month').toDate()};
        $scope.facturando = false;
        $scope.tcdia = 1;
        $scope.dectc = 2;

        empresaSrvc.lstEmpresas().then(function(d){ $scope.lasEmpresas = d; });

        authSrvc.getSession().then(function(usrLogged){
            if(parseInt(usrLogged.workingon) > 0){
                empresaSrvc.getEmpresa(parseInt(usrLogged.workingon)).then(function(d){
                    $scope.laEmpresa = d[0];
                    $scope.dectc = parseInt($scope.laEmpresa.dectc);
                    tipoCambioSrvc.getLastTC().then(function(tc){ $scope.tcdia = parseFloat(tc.lasttc).toFixed($scope.dectc);});
                });
            }
        });

        $scope.getLstCobros = function(){
            facturacionSrvc.getCargos({fal: moment($scope.params.fal).format('YYYY-MM-DD')}).then(function(d){
                for(var x = 0; x < d.length; x++){
                    d[x].id = parseInt(d[x].id);
                    d[x].idcontrato = parseInt(d[x].idcontrato);
                    d[x].fechacobro = moment(d[x].fechacobro).toDate();
                    d[x].monto = parseFloat(d[x].monto);
                    d[x].facturado = parseInt(d[x].facturado);
                    d[x].nocuota = parseInt(d[x].nocuota);
                    d[x].ultimacuota = parseInt(d[x].ultimacuota);
                }
                $scope.cobros = d;
                $scope.facturando = false;
            });
        };

        $scope.facturar = function() {
            $confirm({text: '¿Seguro(a) de facturar los cobros pendientes seleccionados? (Este proceso puede tardar varios minutos)',
                title: 'Generación de facturas a clientes', ok: 'Sí', cancel: 'No'}).then(function() {
                $scope.facturando = true;
                var tmp = [];
                for(var i = 0; i < $scope.cobros.length; i++){
                    if($scope.cobros[i].facturado === 1){
                        $scope.cobros[i].tcdia = $scope.cobros[i].codgface == 'GTQ' ? parseFloat('1.0000').toFixed(4) : $scope.tcdia ;
                        tmp.push($scope.cobros[i]);
                    }
                }

                facturacionSrvc.facturar(tmp).then(function(d){
                    if(d.errores == ''){
                        toaster.pop('success', 'Proceso de facturación terminado', 'Se generaron las facturas ' + d.factgen);
                    }else{
                        var modalInstance = $uibModal.open({
                            animation: true,
                            templateUrl: 'modalErrorGFACE.html',
                            controller: 'errorGFACECtrl',
                            resolve: {
                                errores: function () {
                                    var lst = d.errores.split(',');
                                    var obj = [];
                                    for(var i = 0; i < lst.length; i++){ obj.push({descripcion: lst[i]}); };
                                    return obj;
                                }
                            }
                        });

                        modalInstance.result.then(function() { }, function() {
                            if(d.factgen != ''){
                                toaster.pop('success', 'Proceso de facturación terminado', 'Se generaron las facturas ' + d.factgen);
                            }else{
                                toaster.pop('error', 'Proceso de facturación terminado', 'No se generaron las facturas...');
                            }
                        });
                    }
                    $scope.getLstCobros();
                });
            });
        };

        $scope.getLstCobros();

    }]);
    /*----------------------------------------------------------------------------------------------------------------------------------------------------------*/
    facturacionctrl.controller('errorGFACECtrl', ['$scope', '$uibModalInstance', 'errores', function($scope, $uibModalInstance, errores){
        $scope.errores = errores;
        $scope.ok = function () { $uibModalInstance.dismiss('cancel'); };
    }]);

}());
