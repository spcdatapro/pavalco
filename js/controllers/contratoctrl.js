(function(){

    var contratoctrl = angular.module('cpm.contratoctrl', ['cpm.bancosrvc']);

    contratoctrl.controller('contratoCtrl', ['$scope', 'authSrvc', 'empresaSrvc', '$confirm', 'contratoSrvc', 'clienteSrvc', 'promotorSrvc', 'financieraSrvc', 'tituloSrvc', 'paisSrvc', '$filter', 'DTOptionsBuilder', 'provEquipoSrvc', 'periodicidadSrvc', 'cobroPrimRentaSrvc', 'monedaSrvc', 'toaster', 'sectorSrvc', 'detContSrvc', 'cuentacSrvc', '$uibModal', function($scope, authSrvc, empresaSrvc, $confirm, contratoSrvc, clienteSrvc, promotorSrvc, financieraSrvc, tituloSrvc, paisSrvc, $filter, DTOptionsBuilder, provEquipoSrvc, periodicidadSrvc, cobroPrimRentaSrvc, monedaSrvc, toaster, sectorSrvc, detContSrvc, cuentacSrvc, $uibModal){

        $scope.objEmpresa = {};
        $scope.contratos = [];
        $scope.clientes = [];
        $scope.promotores = [];
        $scope.financieras = [];
        $scope.elContrato = {fechacontrato: moment().toDate()};
        $scope.usrdata = {};
        $scope.detproveq = [];
        $scope.elDetProvEq = {cantidad: 1, preciounitario: 0.0, preciotot: 0.0};
        $scope.provseq = [];
        $scope.elDatoEsp = {};
        $scope.datosespecificos = [];
        $scope.periodos = [];
        $scope.cobprimrenta = [];
        $scope.monedas = [];
        $scope.calcpago = [{id:1, descripcion: 'Vencido'}, {id:2, descripcion: 'Anticipado'}];
        $scope.cargosGenerados = false;
        $scope.contratoStr = '';
        $scope.yaAutorizaron = false;
        $scope.sectores = [];
        $scope.lstdetcont = [];
        $scope.elDetCont = {};
        $scope.lstconfcobros = [];
        $scope.confcobros = {};
        $scope.origen = 6;
        $scope.cuentas = [];
        $scope.cargos = [];

        $scope.dtOptions = DTOptionsBuilder.newOptions().withPaginationType('full_numbers').withBootstrap().withOption('responsive', true).withOption('fnRowCallback', rowCallback);

        clienteSrvc.lstAllClientes().then(function(d){ $scope.clientes = d; });
        promotorSrvc.lstPromotores().then(function(d){ $scope.promotores = d; });
        financieraSrvc.lstAllFinancieras().then(function(d){ $scope.financieras = d; });

        authSrvc.getSession().then(function(usrLogged){
            if(parseInt(usrLogged.workingon) > 0){
                $scope.usrdata = usrLogged;
                empresaSrvc.getEmpresa(parseInt(usrLogged.workingon)).then(function(r){
                    r[0].id = parseInt(r[0].id);
                    $scope.objEmpresa = r[0];
                    $scope.loadContratos($scope.objEmpresa.id);
                });
            }
        });

        $scope.resetElContrato = function(){
            $scope.elContrato = {fechacontrato: moment().toDate()};
            $scope.detproveq = [];
            $scope.elDetProvEq = {cantidad: 1, preciounitario: 0.0, preciotot: 0.0};
            $scope.resetElDatoEsp();
            $scope.contratoStr = '';
            goTop();
        };

        $scope.resetElDatoEsp = function(){
            $scope.elDatoEsp = {
                idcontrato: $scope.elContrato.id, valorequipo: 0.0, enganche: 0.0, idperiodicidad: 0, plazo: 0,
                interes : 0.00, idcalculopago: 0, residual: 0.0, seguroventa: 0.0, segurocosto: 0.0,
                gastosapertura: 0.0, deposegnrentas: 0.0, depseg: 0.0, otros: 0.0, descuentoprov: 0.0,
                iva: 0.0, tasafondeo: 0.0, idcobroprimrenta: 0, incluiriva: 1, rentadiaria: 0,
                floatingprov: 0, opccompfija: 0.0, idmoneda: 0
            };
        };

        $scope.loadContratos = function(idempresa){
            contratoSrvc.lstContratos(idempresa).then(function(d){
                for(var i = 0; i < d.length; i++){
                    d[i].id = parseInt(d[i].id);
                    d[i].idpromotor = parseInt(d[i].idpromotor);
                    d[i].idfinanciera = parseInt(d[i].idfinanciera);
                    d[i].idcliente = parseInt(d[i].idcliente);
                    d[i].idtipofinanciera = parseInt(d[i].idtipofinanciera);
                    d[i].idpais = parseInt(d[i].idpais);
                    d[i].correlativo = parseInt(d[i].correlativo);
                    d[i].fechacontrato = moment(d[i].fechacontrato).toDate();
                    d[i].fhcreacion = moment(d[i].fhcreacion).toDate();
                    d[i].fhactualizacion = moment(d[i].fhactualizacion).toDate();
                }
                $scope.contratos = d;
            });
        };

        $scope.loadDetEq = function(idcontrato){
            contratoSrvc.lstDetProvEq(idcontrato).then(function(deteq){
                for(var j = 0; j < deteq.length; j++){
                    deteq[j].id = parseInt(deteq[j].id);
                    deteq[j].idcontrato = parseInt(deteq[j].idcontrato);
                    deteq[j].idprovequipo = parseInt(deteq[j].idprovequipo);
                    deteq[j].idsector = parseInt(deteq[j].idsector);
                    deteq[j].cantidad = parseInt(deteq[j].cantidad);
                    deteq[j].preciounitario = parseFloat(deteq[j].preciounitario);
                    deteq[j].preciotot = parseFloat(deteq[j].preciotot);
                }
                $scope.detproveq = deteq;
            });
        };

        $scope.loadDetDatosEsp = function(idcontrato){
            $scope.yaAutorizaron = false;
            contratoSrvc.lstDetDatosEsp(idcontrato).then(function(d){
                for(var j = 0; j < d.length; j++){
                    d[j].id = parseInt(d[j].id);
                    d[j].idcontrato = parseInt(d[j].idcontrato);
                    d[j].valorequipo = parseFloat(d[j].valorequipo);
                    d[j].enganche = parseFloat(d[j].enganche);
                    d[j].idperiodicidad = parseInt(d[j].idperiodicidad);
                    d[j].plazo = parseInt(d[j].plazo);
                    d[j].interes = parseFloat(d[j].interes);
                    d[j].idcalculopago = parseInt(d[j].idcalculopago);
                    d[j].residual = parseFloat(d[j].residual);
                    d[j].seguroventa = parseFloat(d[j].seguroventa);
                    d[j].segurocosto = parseFloat(d[j].segurocosto);
                    d[j].gastosapertura = parseFloat(d[j].gastosapertura);
                    d[j].deposegnrentas = parseFloat(d[j].deposegnrentas);
                    d[j].depseg = parseFloat(d[j].depseg);
                    d[j].otros = parseFloat(d[j].otros);
                    d[j].descuentoprov = parseFloat(d[j].descuentoprov);
                    d[j].iva = parseFloat(d[j].iva);
                    d[j].tasafondeo = parseFloat(d[j].tasafondeo);
                    d[j].idcobroprimerarenta = parseInt(d[j].idcobroprimerarenta);
                    d[j].incluiriva = parseInt(d[j].incluiriva);
                    d[j].rentadiaria = parseInt(d[j].rentadiaria);
                    d[j].floatingprov = parseInt(d[j].floatingprov);
                    d[j].opccompfija = parseFloat(d[j].opccompfija);
                    d[j].idmoneda = parseInt(d[j].idmoneda);
                    d[j].autorizada = parseInt(d[j].autorizada) === 1;
                    if(d[j].autorizada){ $scope.yaAutorizaron = true; }
                }
                $scope.datosespecificos = d;
            });
        };

        $scope.loadDatosFact = function(idcontrato){
            contratoSrvc.getDatosFact(idcontrato).then(function(d){
                $scope.elContrato.nit = d[0].nit;
                $scope.elContrato.fechainiciofact = d[0].fechainiciofact != null && d[0].fechainiciofact != undefined ? moment(d[0].fechainiciofact).toDate() : null;
                $scope.elContrato.emailenviofact = d[0].emailenviofact;
                $scope.elContrato.pagoinicial = parseFloat(d[0].pagoinicial);
                $scope.elContrato.cuotamensual = parseFloat(d[0].cuotamensual);
                $scope.elContrato.pagofinal = parseFloat(d[0].pagofinal);
                $scope.elContrato.fhactualizacion = d[0].fhactualizacion != null && d[0].fhactualizacion != undefined ? moment(d[0].fhactualizacion).toDate() : null;
                $scope.elContrato.modificadopor = d[0].modificadopor;
                contratoSrvc.chkForCargos(idcontrato).then(function(e) {
                    $scope.cargosGenerados = parseInt(e.yagenero) === 1;
                });
            });
        };

        function procDetCont(d){
            for(var i = 0; i < d.length; i++){
                d[i].id = parseInt(d[i].id);
                d[i].idcuenta = parseInt(d[i].idcuenta);
                d[i].origen = parseInt(d[i].origen);
                d[i].idorigen = parseInt(d[i].idorigen);
                d[i].debe = parseFloat(parseFloat(d[i].debe).toFixed(2));
                d[i].haber = parseFloat(parseFloat(d[i].haber).toFixed(2));
            }
            return d;
        }

        $scope.loadDetCont = function(idcontrato){
            $scope.lstdetcont = [];
            detContSrvc.lstDetalleCont($scope.origen, parseInt(idcontrato)).then(function(d){
                $scope.lstdetcont = procDetCont(d);
            });
        };

        $scope.loadCargos = function(idcontrato){
            contratoSrvc.lstCargos(idcontrato).then(function(d){
                for(var i = 0; i < d.length; i++){
                    d[i].id = parseInt(d[i].id);
                    d[i].norenta = parseInt(d[i].norenta);
                    d[i].facturado = parseInt(d[i].facturado);
                    d[i].monto = parseFloat(d[i].monto);
                    d[i].fechacobro = moment(d[i].fechacobro).toDate();
                    d[i].idfactura = parseInt(d[i].idfactura);
                }
                $scope.cargos = d;
            });
        };

        $scope.getContrato = function(idcontrato){
            var i = 0;
            contratoSrvc.getContrato(idcontrato).then(function(d){
                d[i].id = parseInt(d[i].id);
                d[i].idpromotor = parseInt(d[i].idpromotor);
                d[i].idfinanciera = parseInt(d[i].idfinanciera);
                d[i].idcliente = parseInt(d[i].idcliente);
                d[i].idtipofinanciera = parseInt(d[i].idtipofinanciera);
                d[i].idpais = parseInt(d[i].idpais);
                d[i].correlativo = parseInt(d[i].correlativo);
                d[i].fechacontrato = moment(d[i].fechacontrato).toDate();
                d[i].fhcreacion = moment(d[i].fhcreacion).toDate();
                d[i].fhactualizacion = moment(d[i].fhactualizacion).toDate();
                $scope.elContrato = d[i];
                $scope.elContrato.objPromotor = [$filter('getById')($scope.promotores, $scope.elContrato.idpromotor)];
                $scope.elContrato.objFinanciera = [$filter('getById')($scope.financieras, $scope.elContrato.idfinanciera)];
                $scope.elContrato.objCliente = [$filter('getById')($scope.clientes, $scope.elContrato.idcliente)];
                $scope.contratoStr = 'No. GCF' + $filter('padNumber')($scope.elContrato.idcliente, 4) + '-' + $filter('padNumber')($scope.elContrato.correlativo, 4) + ', ';
                $scope.contratoStr += $scope.elContrato.objCliente[0].nombre;
                provEquipoSrvc.lstProvsEq().then(function(d){ $scope.provseq = d; });
                periodicidadSrvc.lstPeriodicidad().then(function(d){ $scope.periodos = d; });
                cobroPrimRentaSrvc.lstCobroPrimRenta().then(function(d){ $scope.cobprimrenta = d; });
                monedaSrvc.lstMonedas().then(function(d){ $scope.monedas = d; });
                sectorSrvc.lstSectores().then(function(d){ $scope.sectores = d; });
                $scope.loadDetDatosEsp($scope.elContrato.id);
                $scope.loadDatosFact($scope.elContrato.id);
                $scope.loadCargos($scope.elContrato.id);
                $scope.loadDetEq(idcontrato);
                cuentacSrvc.getByTipo($scope.objEmpresa.id, 0).then(function(d){ $scope.cuentas = d; });
                $scope.loadDetCont(idcontrato);
                $scope.resetElDatoEsp();
                $scope.loadConfCargos(idcontrato);
                $scope.resetConfCobros();

                goTop();
            });
        };

        $scope.addContrato = function(obj){
            obj.idempresa = $scope.objEmpresa.id;
            obj.idpromotor = parseInt(obj.objPromotor[0].id);
            obj.idfinanciera = parseInt(obj.objFinanciera[0].id);
            obj.idcliente = parseInt(obj.objCliente[0].id);
            obj.usuario = $scope.usrdata.usuario;
            obj.fechacontratostr = moment(obj.fechacontrato).format('YYYY-MM-DD');
            contratoSrvc.editRow(obj, 'c').then(function(d){
                $scope.loadContratos($scope.objEmpresa.id);
                $scope.getContrato(parseInt(d.lastid));
            });
        };

        $scope.delContrato = function(idcontrato){
            $confirm({text: '¿Seguro(a) de eliminar este contrato?', title: 'Eliminar contrato', ok: 'Sí', cancel: 'No'}).then(function() {
                contratoSrvc.editRow({id: idcontrato}, 'd').then(function(){ $scope.loadContratos($scope.objEmpresa.id); });
            });
        };

        $scope.calcTotDetEq = function(){ $scope.elDetProvEq.preciotot = parseInt($scope.elDetProvEq.cantidad) * parseFloat($scope.elDetProvEq.preciounitario); };

        $scope.addDetEq = function(obj) {
            obj.idcontrato = $scope.elContrato.id;
            obj.idprovequipo = obj.objProvEq[0].id;
            obj.idsector = obj.objSector.id;
            contratoSrvc.editRow(obj, 'ce').then(function(){
                $scope.loadDetEq($scope.elContrato.id);
                $scope.elDetProvEq = {cantidad: 1, preciounitario: 0.0, preciotot: 0.0};
            });
        };

        $scope.delDetEq = function(iddeteq){
            $confirm({text: '¿Seguro(a) de eliminar este proveedor?', title: 'Eliminar proveedor', ok: 'Sí', cancel: 'No'}).then(function() {
                contratoSrvc.editRow({id: iddeteq}, 'de').then(function(){ $scope.loadDetEq($scope.elContrato.id); });
            });
        };

        $scope.addDatoEsp = function(obj){
            obj.idperiodicidad = obj.objPeriodicidad != null && obj.objPeriodicidad != undefined ? obj.objPeriodicidad.id : 0;
            obj.idcalculopago = obj.objCalculoPago != null && obj.objCalculoPago != undefined ? obj.objCalculoPago.id : 0;
            obj.idcobroprimrenta = obj.objCobPrimRenta != null && obj.objCobPrimRenta != undefined ? obj.objCobPrimRenta.id : 0;
            obj.idmoneda = obj.objMoneda != null && obj.objMoneda != undefined ? obj.objMoneda.id : 0;
            contratoSrvc.editRow(obj, 'cs').then(function(){
                $scope.loadDetDatosEsp($scope.elContrato.id);
                $scope.resetElDatoEsp();
            });
        };

        $scope.delDetEsp = function(iddetesp){
            $confirm({text: '¿Seguro(a) de eliminar este dato?', title: 'Eliminar dato', ok: 'Sí', cancel: 'No'}).then(function() {
                contratoSrvc.editRow({id: iddetesp}, 'ds').then(function(){ $scope.loadDetDatosEsp($scope.elContrato.id); });
            });
        };

        $scope.autorizar = function(obj){
            $confirm({
                text: '¿Seguro(a) de autorizar esta cotización?',
                title: 'Autorización de cotización',
                ok: 'Sí',
                cancel: 'No'
            }).then(function() {
                contratoSrvc.autorizar({id: obj.id, idcontrato: obj.idcontrato}).then(function(){
                    $scope.loadDetDatosEsp(obj.idcontrato);
                    $scope.loadDatosFact(obj.idcontrato);
                    goTop();
                });
            });
        };

        $scope.updDatosFact = function(obj){
            obj.usuario = $scope.usrdata.usuario;
            obj.fechainiciofactstr = moment(obj.fechainiciofact).format('YYYY-MM-DD');
            obj.pagoinicial = 0.00; obj.cuotamensual = 0.00; obj.pagofinal = 0.00;
            contratoSrvc.editRow(obj, 'uf').then(function(){ $scope.loadDatosFact(obj.id); });
        };

        $scope.delDatosFact = function(obj){
            $confirm({text: '¿Seguro(a) de eliminar los datos de facturación?', title: 'Eliminar datos de facturación', ok: 'Sí', cancel: 'No'}).then(function() {
                obj.usuario = $scope.usrdata.usuario;
                contratoSrvc.editRow(obj, 'df').then(function(){ $scope.loadDatosFact(obj.id); });
            });
        };

        $scope.genCargos = function(){
            $confirm({text: '¿Seguro(a) de generar los cargos?', title: 'Generación de cargos del contrato', ok: 'Sí', cancel: 'No'}).then(function() {
                var obj = {idcontrato: $scope.elContrato.id};
                $scope.cargosGenerados = true;
                contratoSrvc.genCargos(obj).then(function(d){
                    $scope.cargosGenerados = parseInt(d.yagenero) === 1;
                    toaster.pop('success', 'Generación de cargos', 'Proceso terminado. Se generaron ' + d.generados + ' cargos pendientes de facturar.');
                });
            });
        };

        $scope.verCargos = function(){
            var modalInstance = $uibModal.open({
                animation: true,
                templateUrl: 'modalCargos.html',
                controller: 'ModalCargosCtrl',
                resolve:{
                    cargos: function(){ return $scope.cargos; },
                    contrato: function(){ return $scope.elContrato; }
                }
            });

            modalInstance.result.then(function(){
                console.log('Closed modal...');
            }, function(){ return 0; });
        };

        function setDetConfCargosData(d){
            if(d != null && d != undefined){
                for(var j = 0; j < d.length; j++){
                    d[j].id = parseInt(d[j].id);
                    d[j].idconfcargocont = parseInt(d[j].idconfcargocont);
                    d[j].cantidad = parseInt(d[j].cantidad);
                    d[j].preciounitario = parseFloat(parseFloat(d[j].preciounitario).toFixed(2));
                    d[j].precio = parseFloat(parseFloat(d[j].precio).toFixed(2));
                }
            }
            return d;
        }

        function setConfCargosData(d){
            for(var i = 0; i < d.length; i++){
                d[i].id = parseInt(d[i].id);
                d[i].idcontrato = parseInt(d[i].idcontrato);
                d[i].cantidad = parseInt(d[i].cantidad);
                d[i].ordengenera = parseInt(d[i].ordengenera);
                d[i].monto = parseFloat(parseFloat(d[i].monto).toFixed(2));
                d[i].tipocargo = parseInt(d[i].tipocargo);
                d[i].detalle = setDetConfCargosData(d[i].detalle);
            }
            return d;
        }

        $scope.resetConfCobros = function(){
            $scope.confcobros = { idcontrato: +$scope.elContrato.id > 0 ? +$scope.elContrato.id : 0, cantidad: +$scope.elDatoEsp.plazo > 0 ? +$scope.elDatoEsp.plazo : 1, tipocargo: 0,
                monto: 0.00, ordengenera: 0,  detalle: []
            }
        };

        $scope.loadConfCargos = function(idcontrato){
            contratoSrvc.lstConfigCargos(idcontrato).then(function(d){
                $scope.lstconfcobros = setConfCargosData(d);
            });
        };

        $scope.getConfCargo = function(idconfcargo){
            contratoSrvc.getConfigCargos(idconfcargo).then(function(d){
                $scope.confcobros = setConfCargosData(d)[0];
            });
        };

        $scope.addConfCargos = function(obj){
            obj.idcontrato = +$scope.elContrato.id;
            contratoSrvc.editRow(obj, '/cc').then(function(d){
                $scope.loadConfCargos(obj.idcontrato);
                $scope.getConfCargo(+d.lastid);
            });
        };

        $scope.updConfCargos = function(obj){
            obj.idcontrato = +$scope.elContrato.id;
            contratoSrvc.editRow(obj, '/uc').then(function(){
                $scope.loadConfCargos(obj.idcontrato);
                $scope.getConfCargo(obj.id);
            });
        };

        $scope.delConfCargos = function(obj){
            $confirm({text: '¿Seguro(a) de eliminar esta configuración?', title: 'Eliminar configuración', ok: 'Sí', cancel: 'No'}).then(function() {
                contratoSrvc.editRow(obj, '/dc').then(function(){ $scope.loadConfCargos($scope.elContrato.id); $scope.resetConfCobros(); });
            });
        };

        $scope.verDetalle = function(obj){
            var modalInstance = $uibModal.open({
                animation: true,
                templateUrl: 'modalDetConfCobros.html',
                controller: 'ModalDetConfCobrosCtrl',
                windowClass: 'app-modal-window',
                resolve:{
                    config: function(){ return obj; },
                    generados: function(){return $scope.cargosGenerados; }
                }
            });

            modalInstance.result.then(function(){
                console.log('Closed modal...');
            }, function(){ return 0; });
        };


        $scope.zeroDebe = function(valor){ $scope.elDetCont.debe = parseFloat(valor) > 0 ? 0.0 : $scope.elDetCont.debe; };
        $scope.zeroHaber = function(valor){ $scope.elDetCont.haber = parseFloat(valor) > 0 ? 0.0 : $scope.elDetCont.haber; };

        $scope.addDetCont = function(obj){
            obj.origen = $scope.origen;
            obj.idorigen = parseInt($scope.elContrato.id);
            obj.debe = parseFloat(obj.debe);
            obj.haber = parseFloat(obj.haber);
            obj.idcuenta = parseInt(obj.objCuenta[0].id);
            detContSrvc.editRow(obj, 'c').then(function(){
                detContSrvc.lstDetalleCont($scope.origen, parseInt($scope.elContrato.id)).then(function(detc){
                    $scope.lstdetcont = procDetCont(detc);
                    $scope.elDetCont = {debe: 0.0, haber: 0.0};
                    $scope.searchcta = "";
                });
            });
        };

        $scope.delDetCont = function(obj){
            $confirm({text: '¿Seguro(a) de eliminar esta cuenta?', title: 'Eliminar cuenta contable', ok: 'Sí', cancel: 'No'}).then(function() {
                detContSrvc.editRow({id:obj.id}, 'd').then(function(){ $scope.loadDetCont(obj.idorigen); });
            });
        };

    }]);
    //------------------------------------------------------------------------------------------------------------------------------------------------//
    contratoctrl.controller('ModalDetConfCobrosCtrl', ['$scope', '$uibModalInstance', 'contratoSrvc', '$confirm', 'config', 'generados', function($scope, $uibModalInstance, contratoSrvc, $confirm, config, generados){
        $scope.config = config;
        $scope.generados = generados;
        $scope.detcnf = {};

        $scope.ok = function () {
            //$uibModalInstance.close($scope.selectedCta[0]);
            $uibModalInstance.close();
        };

        $scope.cancel = function () {
            $uibModalInstance.dismiss('cancel');
        };

        $scope.resetDet = function(){
            $scope.detcnf = {
                idconfcargocont : $scope.config.id,
                cantidad: 1
            };
        };

        function setDetConf(d){
            if(d != null && d != undefined){
                for(var j = 0; j < d.length; j++){
                    d[j].id = parseInt(d[j].id);
                    d[j].idconfcargocont = parseInt(d[j].idconfcargocont);
                    d[j].cantidad = parseInt(d[j].cantidad);
                    d[j].preciounitario = parseFloat(parseFloat(d[j].preciounitario).toFixed(2));
                    d[j].precio = parseFloat(parseFloat(d[j].precio).toFixed(2));
                }
            }
            return d;
        }

        $scope.loadDet = function(){
            contratoSrvc.lstDetConfigCargos($scope.config.id).then(function(d){
                $scope.config.detalle = setDetConf(d);
            });
        };

        $scope.getDet = function(iddet){
            contratoSrvc.getDetConfigCobros(iddet).then(function(d){
                $scope.detcnf = setDetConf(d)[0];
            });
        };

        $scope.addDet = function(obj){
            obj.idconfcargocont = $scope.config.id;
            obj.precio = parseFloat((obj.cantidad * obj.preciounitario).toFixed(2));
            contratoSrvc.editRow(obj, 'cdc').then(function(d){
                $scope.loadDet();
                $scope.getDet(+d.lastid);
            });
        };

        $scope.updDet = function(obj){
            obj.precio = parseFloat((obj.cantidad * obj.preciounitario).toFixed(2));
            contratoSrvc.editRow(obj, 'udc').then(function(d){
                $scope.loadDet();
                $scope.getDet(obj.id);
            });
        };

        $scope.delDet = function(obj){
            $confirm({text: '¿Seguro(a) de eliminar este detalle?', title: 'Eliminar detalle', ok: 'Sí', cancel: 'No'}).then(function() {
                contratoSrvc.editRow({id:obj.id}, 'ddc').then(function(){ $scope.loadDet(); $scope.resetDet(); });
            });
        };

        $scope.resetDet();

    }]);

    //------------------------------------------------------------------------------------------------------------------------------------------------//
    contratoctrl.controller('ModalCargosCtrl', ['$scope', '$uibModalInstance', 'contratoSrvc', 'toaster', 'cargos', 'contrato', '$filter', function($scope, $uibModalInstance, contratoSrvc, toaster, cargos, contrato, $filter){
        $scope.cargos = cargos;
        $scope.contrato = contrato;

        $scope.ok = function () {
            //$uibModalInstance.close($scope.selectedCta[0]);
            $uibModalInstance.close();
        };

        $scope.cancel = function () {
            $uibModalInstance.dismiss('cancel');
        };

        $scope.setFacturado = function(obj){
            contratoSrvc.editRow({id: obj.id, facturado: (obj.facturado != null && obj.facturado != undefined ? obj.facturado : 0) }, 'setpagado').then(function(){
                toaster.pop('success', 'Estatus de cargo', 'El cargo con fecha '+ $filter('date')(obj.fechacobro, 'dd/MM/yyyy') +' cambio estatus a ' + (+obj.facturado == 1 ? 'facturado' : 'no facturado'));
            });
        };

    }]);

}());
