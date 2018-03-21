(function(){

    var ventactrl = angular.module('cpm.ventactrl', []);

    ventactrl.controller('ventaCtrl', ['$scope', '$filter', 'ventaSrvc', 'authSrvc', 'empresaSrvc', 'DTOptionsBuilder', 'clienteSrvc', 'tipoCompraSrvc', 'toaster', 'cuentacSrvc', 'detContSrvc', '$uibModal', '$confirm', 'monedaSrvc', 'tipoFacturaSrvc', 'contratoSrvc', 'razonAnulacionSrvc', function($scope, $filter, ventaSrvc, authSrvc, empresaSrvc, DTOptionsBuilder, clienteSrvc, tipoCompraSrvc, toaster, cuentacSrvc, detContSrvc, $uibModal, $confirm, monedaSrvc, tipoFacturaSrvc, contratoSrvc, razonAnulacionSrvc){

        $scope.idempresa = 0;
        $scope.ventas = [];
        $scope.venta = {};
        $scope.clientes = [];
        $scope.contratos = [];
        $scope.losTiposCompra = [];
        $scope.origen = 3;
        $scope.monedas = [];
        $scope.dectc = 2;
        $scope.lsttiposfact = [];
        $scope.losDetCont = [];
        $scope.elDetCont = {debe: 0.0, haber: 0.0};
        $scope.cuentas = [];
        $scope.ventastr = '';
        $scope.razonesanula = [];

        $scope.dtOptions = DTOptionsBuilder.newOptions().withPaginationType('full_numbers').withBootstrap().withOption('responsive', true).withOption('fnRowCallback', rowCallback);

        authSrvc.getSession().then(function(usrLogged){
            $scope.idempresa = parseInt(usrLogged.workingon);
            if($scope.idempresa > 0){
                empresaSrvc.getEmpresa($scope.idempresa).then(function(d){
                    $scope.dectc = parseInt(d[0].dectc);
                    monedaSrvc.lstMonedas().then(function(l){
                        $scope.monedas = l;
                        $scope.resetVenta();
                        $scope.getLstVentas($scope.idempresa);
                    });
                });
            }
        });

        tipoFacturaSrvc.lstTiposFactura().then(function(d){
            for(var i = 0; i < d.length; i++){ d[i].id = parseInt(d[i].id); d[i].paraventa = parseInt(d[i].paraventa); }
            $scope.lsttiposfact = d;
        });

        tipoCompraSrvc.lstTiposCompra().then(function(d){
            for(var i = 0; i < d.length; i++){ d[i].id = parseInt(d[i].id); d[i].paraventa = parseInt(d[i].paraventa); }
            $scope.losTiposCompra = d;
        });

        clienteSrvc.lstAllClientes().then(function(d){ $scope.clientes = d; });

        razonAnulacionSrvc.lstRazones().then(function(d){$scope.razonesanula = d; });

        $scope.getContratosByCliente = function(idcliente){ contratoSrvc.lstContratosByEmpByCli($scope.idempresa, idcliente).then(function(d){ $scope.contratos = d; }) };

        $scope.calcular = function(){
            var geniva = true;
            var totFact = $scope.venta.total != null && $scope.venta.total != undefined ? parseFloat(parseFloat($scope.venta.total).toFixed(2)) : 0.00;
            var noAfecto = 0.00;

            if($scope.venta.objTipoFactura != null && $scope.venta.objTipoFactura != undefined){ geniva = parseInt($scope.venta.objTipoFactura.generaiva) === 1; }
            //if($scope.venta.objTipoCompra != null && $scope.venta.objTipoCompra != undefined){ genidp = $scope.venta.objTipoCompra.id === 3; }

            if(noAfecto <= totFact){
                $scope.venta.subtotal = geniva ? parseFloat((totFact / 1.12).toFixed(2)) : totFact;
                $scope.venta.iva = geniva ? parseFloat((totFact - $scope.venta.subtotal).toFixed(2)) : 0.00;
            }else{
                $scope.venta.noafecto = 0;
                toaster.pop({ type: 'error', title: 'Error en el monto de No afecto.',
                    body: 'El monto de No afecto no puede ser mayor al total de la factura.', timeout: 7000 });
            }
        };

        $scope.$watch('venta.objCliente', function(newValue, oldValue){
            if(newValue != null && newValue != undefined){
                contratoSrvc.lstContratosByEmpByCli($scope.idempresa, parseInt(newValue[0].id)).then(function(d){
                    $scope.contratos = d;
                    if($scope.venta.id > 0){
                        $scope.venta.objContrato = [$filter('getById')($scope.contratos, $scope.venta.idcontrato)];
                        $scope.ventastr = newValue[0].nombre + ', ' + $scope.venta.nocontrato + ', ' + $scope.venta.serie + ' - ' + $scope.venta.numero + ', ' +
                            $scope.venta.moneda + ' ' + $filter('number')($scope.venta.total, 2);
                    }
                })
            }
        });

        $scope.setTipoCambio = function(qmoneda){ $scope.venta.tipocambio = parseFloat(qmoneda.tipocambio).toFixed($scope.dectc); };

        $scope.resetVenta = function(){
            $scope.venta = {idtipofactura: 0, idcontrato: 0, idcliente: 0, serie: '', numero: '', fechaingreso: moment().toDate(), mesiva: moment().month() + 1, fecha: moment().toDate(), idtipoventa: 0,
                conceptomayor: '', total: 0.00, subtotal: 0.00, iva: 0.00, idmoneda: 0, tipocambio: 0, objTipoFactura: null, objContrato: null, objCliente: null, objTipoVenta: null, objMoneda: null};
            $scope.contratos = [];
            $scope.ventastr = '';
            $scope.losDetCont = [];
            $scope.elDetCont = {debe: 0.0, haber: 0.0};
        };

        function procDataVenta(d){
            for(var i = 0; i < d.length; i++){
                d[i].id = parseInt(d[i].id);
                d[i].idtipofactura = parseInt(d[i].idtipofactura);
                d[i].idcontrato = parseInt(d[i].idcontrato);
                d[i].idcliente = parseInt(d[i].idcliente);
                d[i].mesiva = parseInt(d[i].mesiva);
                d[i].idtipoventa = parseInt(d[i].idtipoventa);
                d[i].idmoneda = parseInt(d[i].idmoneda);
                d[i].anulada = parseInt(d[i].anulada);
                d[i].idrazonanulafactura = parseInt(d[i].idrazonanulafactura);
                d[i].fechaingreso = moment(d[i].fechaingreso).toDate();
                d[i].fecha = moment(d[i].fecha).toDate();
                d[i].fechaanula = moment(d[i].fechaanula).isValid() ? moment(d[i].fechaanula).toDate() : null;
                d[i].total = parseFloat(parseFloat(d[i].total).toFixed(2));
                d[i].subtotal = parseFloat(parseFloat(d[i].subtotal).toFixed(2));
                d[i].iva = parseFloat(parseFloat(d[i].iva).toFixed(2));
                d[i].tipocambio = parseFloat(parseFloat(d[i].tipocambio).toFixed($scope.dectc));
            }
            return d;
        }

        $scope.getLstVentas = function(idempresa){
            ventaSrvc.lstVentas(idempresa).then(function(d){
                $scope.ventas = procDataVenta(d);
            });
        };

        $scope.getVenta = function(idventa){
            ventaSrvc.getVenta(idventa).then(function(d){
                $scope.venta = procDataVenta(d)[0];
                $scope.venta.objTipoFactura = $filter('getById')($scope.lsttiposfact, $scope.venta.idtipofactura);
                $scope.venta.objCliente = [$filter('getById')($scope.clientes, $scope.venta.idcliente)];
                $scope.venta.objTipoVenta = $filter('getById')($scope.losTiposCompra, $scope.venta.idtipoventa);
                $scope.venta.objMoneda = $filter('getById')($scope.monedas, $scope.venta.idmoneda);
                $scope.elDetCont = {debe: 0.0, haber: 0.0};
                cuentacSrvc.getByTipo($scope.idempresa, 0).then(function(d){ $scope.cuentas = d; });
                $scope.getDetalleContable(parseInt(idventa));
                goTop();
            });
        };

        function prepVenta(obj){
            obj.idtipofactura = obj.objTipoFactura.id;
            //obj.idcontrato = obj.objContrato[0].id;
            obj.idcontrato = obj.objContrato != null && obj.objContrato != undefined ? (obj.objContrato[0] != null && obj.objContrato[0] != undefined ? obj.objContrato[0].id : 0) : 0;
            obj.idcliente = obj.objCliente[0].id;
            obj.fechaingresostr = moment(obj.fechaingreso).format('YYYY-MM-DD');
            obj.fechastr = moment(obj.fecha).format('YYYY-MM-DD');
            obj.mesiva = moment(obj.fecha).month() + 1;
            obj.idtipoventa = obj.objTipoVenta.id;
            obj.idmoneda = obj.objMoneda.id;
            obj.idempresa = $scope.idempresa;
            return obj;
        }

        $scope.addVenta = function(obj){
            obj = prepVenta(obj);
            //console.log(obj); return;
            ventaSrvc.editRow(obj, 'c').then(function(d){
                $scope.getLstVentas($scope.idempresa);
                $scope.getVenta(parseInt(d.lastid));
            });
        };

        $scope.updVenta = function(obj){
            obj = prepVenta(obj);
            ventaSrvc.editRow(obj, 'u').then(function(d){
                $scope.getLstVentas($scope.idempresa);
                $scope.getVenta(parseInt(d.lastid));
            })
        };

        $scope.delVenta = function(obj){
            $confirm({text: '¿Seguro(a) de eliminar esta factura de venta?', title: 'Eliminar factura de venta', ok: 'Sí', cancel: 'No'}).then(function() {
                ventaSrvc.editRow(obj, 'd').then(function(d){ $scope.getLstVentas($scope.idempresa); $scope.resetVenta(); });
            });
        };

        $scope.anular = function(obj){
            var modalInstance = $uibModal.open({
                animation: true,
                templateUrl: 'modalAnulacion.html',
                controller: 'ModalAnulaVentaCtrl',
                resolve:{
                    lstrazonanula: function(){
                        return $scope.razonesanula;
                    }
                }
            });

            modalInstance.result.then(function(datosAnula){
                //console.log(datosAnula);
                obj.idrazonanulacion = datosAnula.idrazonanulacion;
                obj.fechaanulastr = datosAnula.fechaanulastr;
                //console.log(obj);
                ventaSrvc.editRow(obj, 'anula').then(function(){ $scope.getVenta($scope.venta.id); });
            }, function(){ return 0; });
        };

        function procDataDet(data){
            for(var i = 0; i < data.length; i++){
                data[i].id = parseInt(data[i].id);
                data[i].origen = parseInt(data[i].origen);
                data[i].idorigen = parseInt(data[i].idorigen);
                data[i].idcuenta = parseInt(data[i].idcuenta);
                data[i].debe = parseFloat(parseFloat(data[i].debe).toFixed(2));
                data[i].haber = parseFloat(parseFloat(data[i].haber).toFixed(2));
            }
            return data;
        }

        $scope.getDetalleContable = function(idventa){
            detContSrvc.lstDetalleCont($scope.origen, idventa).then(function(d){
                $scope.losDetCont = procDataDet(d);
            });
        };

        $scope.zeroDebe = function(valor){ $scope.elDetCont.debe = parseFloat(valor) > 0 ? 0.0 : $scope.elDetCont.debe; };
        $scope.zeroHaber = function(valor){ $scope.elDetCont.haber = parseFloat(valor) > 0 ? 0.0 : $scope.elDetCont.haber; };

        $scope.addDetCont = function(obj){
            obj.origen = $scope.origen;
            obj.idorigen = parseInt($scope.venta.id);
            obj.debe = parseFloat(parseFloat(obj.debe).toFixed(2));
            obj.haber = parseFloat(parseFloat(obj.haber).toFixed(2));
            obj.idcuenta = parseInt(obj.objCuenta[0].id);
            detContSrvc.editRow(obj, 'c').then(function(){
                detContSrvc.lstDetalleCont($scope.origen, parseInt($scope.venta.id)).then(function(detc){
                    $scope.losDetCont = procDataDet(detc);
                    $scope.elDetCont = {debe: 0.0, haber: 0.0};
                    $scope.searchcta = "";
                });
            });
        };

        $scope.delDetCont = function(obj){
            $confirm({text: '¿Seguro(a) de eliminar esta cuenta?', title: 'Eliminar cuenta contable', ok: 'Sí', cancel: 'No'}).then(function() {
                detContSrvc.editRow({id:obj.id}, 'd').then(function(){ $scope.getDetalleContable(obj.idorigen); });
            });
        };

        $scope.printVersion = function(){ PrintElem('#toPrint', 'Factura de venta'); };

    }]);
    //------------------------------------------------------------------------------------------------------------------------------------------------//
    ventactrl.controller('ModalAnulaVentaCtrl', ['$scope', '$uibModalInstance', 'lstrazonanula', function($scope, $uibModalInstance, lstrazonanula){
        $scope.razones = lstrazonanula;
        $scope.razon = [];
        $scope.anuladata = {idrazonanulacion:0, fechaanula: moment().toDate()};

        $scope.ok = function () {
            $scope.anuladata.idrazonanulacion = $scope.razon.id;
            $scope.anuladata.fechaanulastr = moment($scope.anuladata.fechaanula).format('YYYY-MM-DD');
            $uibModalInstance.close($scope.anuladata);
        };

        $scope.cancel = function () {
            $uibModalInstance.dismiss('cancel');
        };
    }]);


}());
