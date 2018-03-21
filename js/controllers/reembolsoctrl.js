(function(){

    var reembolsoctrl = angular.module('cpm.reembolsoctrl', []);

    reembolsoctrl.controller('reembolsoCtrl', ['$scope', 'reembolsoSrvc', 'monedaSrvc', 'authSrvc', 'empresaSrvc', '$route', '$confirm', 'tipoReembolsoSrvc', 'DTOptionsBuilder', '$filter', 'tipoFacturaSrvc', 'tipoCompraSrvc', 'detContSrvc', 'cuentacSrvc', 'toaster', '$uibModal', 'tipoMovTranBanSrvc', 'bancoSrvc', 'beneficiarioSrvc', 'tipoCombustibleSrvc', function($scope, reembolsoSrvc, monedaSrvc, authSrvc, empresaSrvc, $route, $confirm, tipoReembolsoSrvc, DTOptionsBuilder, $filter, tipoFacturaSrvc, tipoCompraSrvc, detContSrvc, cuentacSrvc, toaster, $uibModal, tipoMovTranBanSrvc, bancoSrvc, beneficiarioSrvc, tipoCombustibleSrvc){

        $scope.monedas = [];
        $scope.dectc = 2;
        $scope.permiso = {};
        $scope.reembolsos = [];
        $scope.reembolso = {};
        $scope.tiposreembolso = [];
        $scope.compras = [];
        $scope.compra = {};
        $scope.tiposfactura = [];
        $scope.tiposcompra = [];
        $scope.reemstr = '';
        $scope.comprastr = '';
        $scope.origen = 2;
        $scope.detcont = {debe:0.00, haber:0.00};
        $scope.cuentasc = [];
        $scope.beneficiarios = [];
        $scope.tiposmov = [];
        $scope.bancos = [];
        $scope.detcontreem = [];
        $scope.origenReembolsos = 5;
        $scope.tranban = [];
        $scope.dataToPrint = [];
        $scope.total = {debe: 0.00, haber: 0.00};
        $scope.combustibles = [];
        $scope.partidaCuadrada = -1;

        $scope.dtOptions = DTOptionsBuilder.newOptions().withPaginationType('full_numbers').withBootstrap()
            .withBootstrapOptions({
                pagination: {
                    classes: {
                        ul: 'pagination pagination-sm'
                    }
                }
            })
            .withOption('ordering', false)
            .withOption('responsive', true);

        $scope.dtOptionsDetCont = DTOptionsBuilder.newOptions().withBootstrap()
            .withBootstrapOptions({
                pagination: {
                    classes: {
                        ul: 'pagination pagination-sm'
                    }
                }
            })
            .withOption('responsive', true)
            .withOption('paging', false)
            .withOption('searching', false)
            .withOption('info', false)
            .withOption('ordering', false)
            .withOption('fnRowCallback', rowCallback);

        authSrvc.getSession().then(function(usrLogged){
            if(parseInt(usrLogged.workingon) > 0){
                authSrvc.gpr({idusuario: parseInt(usrLogged.uid), ruta:$route.current.params.name}).then(function(d){
                    $scope.permiso = d;
                    empresaSrvc.getEmpresa(parseInt(usrLogged.workingon)).then(function(d){
                        //$scope.reembolso.objEmpresa = d[0];
                        $scope.reembolso.idempresa = parseInt(d[0].id);
                        $scope.dectc = parseInt(d[0].dectc);
                        $scope.resetReembolso();
                        $scope.resetCompra();
                        $scope.getLstReembolsos();
                    });
                });
            }
        });

        tipoReembolsoSrvc.lstTiposReembolso().then(function(d){ $scope.tiposreembolso = d; });

        tipoFacturaSrvc.lstTiposFactura().then(function(d){
            for(var i = 0; i < d.length; i++){
                d[i].generaiva = parseInt(d[i].generaiva) === 1;
                d[i].paracompra = parseInt(d[i].paracompra);
            }
            $scope.tiposfactura = d;
        });

        tipoCompraSrvc.lstTiposCompra().then(function(d){ $scope.tiposcompra = d; });

        beneficiarioSrvc.lstBeneficiarios().then(function(d){ $scope.beneficiarios = d; });

        tipoCombustibleSrvc.lstTiposCombustible().then(function(d){
            for(var i = 0; i < d.length; i++){
                d[i].id = parseInt(d[i].id);
                d[i].impuesto = parseFloat(parseFloat(d[i].impuesto).toFixed(2));
            }
            $scope.combustibles = d;
        });

        function procDataReemb(d){
            for(var i = 0; i < d.length; i++){
                d[i].id = parseInt(d[i].id);
                d[i].idempresa = parseInt(d[i].idempresa);
                d[i].finicio = moment(d[i].finicio).toDate();
                d[i].ffin = !moment(d[i].ffin).isValid() ? null : moment(d[i].ffin).toDate();
                d[i].estatus = parseInt(d[i].estatus);
                d[i].idtiporeembolso = parseInt(d[i].idtiporeembolso);
                d[i].idbeneficiario = parseInt(d[i].idbeneficiario);
                d[i].totreembolso = parseFloat(parseFloat(d[i].totreembolso).toFixed(2));
            }
            return d;
        }

        function procDataCompras(d){
            for(var i = 0; i < d.length; i++){
                d[i].id = parseInt(d[i].id);
                d[i].idempresa = parseInt(d[i].idempresa);
                d[i].idreembolso = parseInt(d[i].idreembolso);
                d[i].idtipofactura = parseInt(d[i].idtipofactura);
                d[i].documento = parseInt(d[i].documento);
                d[i].fechaingreso = moment(d[i].fechaingreso).toDate();
                d[i].mesiva = parseInt(d[i].mesiva);
                d[i].fechafactura = moment(d[i].fechafactura).toDate();
                d[i].idtipocompra = parseInt(d[i].idtipocompra);
                d[i].idtipocombustible = parseInt(d[i].idtipocombustible);
                d[i].totfact = parseFloat(d[i].totfact).toFixed(2);
                d[i].subtotal = parseFloat(d[i].subtotal).toFixed(2);
                d[i].iva = parseFloat(d[i].iva).toFixed(2);
                d[i].idmoneda = parseInt(d[i].idmoneda);
                d[i].tipocambio = parseFloat(d[i].tipocambio).toFixed($scope.dectc);
                d[i].idproveedor = parseInt(d[i].idproveedor);
                d[i].retenerisr = parseInt(d[i].retenerisr);
                d[i].isr = parseFloat(d[i].isr).toFixed(2);
                d[i].idp = parseFloat(d[i].idp).toFixed(2);
                d[i].galones = parseFloat(d[i].galones).toFixed(2);
                d[i].detcont = [];
            }
            return d;
        }

        $scope.resetReembolso = function(){
            $scope.reembolso = {
                idempresa: $scope.reembolso.idempresa,
                finicio: moment().toDate(),
                ffin: null,
                beneficiario: '',
                idbeneficiario: 0,
                tblbeneficiario: '',
                estatus: 1
            };
            $scope.reemstr = '';
            $scope.resetCompra();
            $scope.detcontreem = [];
            $scope.tranban = [];
            goTop();
        };

        $scope.resetCompra = function(){
            $scope.compra = {
                idempresa: parseInt($scope.reembolso.idempresa),
                idreembolso: 0,
                idproveedor: 0,
                proveedor: '',
                nit: '',
                fechaingreso: moment().toDate(),
                mesiva: moment().month() + 1,
                fechafactura: moment().toDate(),
                idtipocompra: 0,
                totfact: 0.00,
                noafecto: 0.00,
                subtotal: 0.00,
                iva: 0.00,
                idmoneda: 1,
                tipocambio: parseFloat('1').toFixed($scope.dectc),
                objTipoFactura: [],
                conceptomayor: '',
                retenerisr: 0,
                isr: 0.00,
                objTipoCombustible: [],
                idtipocombustible: 0,
                galones: 0.00,
                idp: 0.00
            };
            $scope.comprastr = '';
            goTop();
        };

        $scope.getLstReembolsos = function(){
            reembolsoSrvc.lstReembolsos($scope.reembolso.idempresa).then(function(d){
                $scope.reembolsos = procDataReemb(d);
            });
        };

        $scope.getDetReem = function(idreem){
            reembolsoSrvc.lstCompras(idreem).then(function(d){
                $scope.compras = procDataCompras(d);
                cuentacSrvc.getByTipo($scope.reembolso.idempresa, 0).then(function(d){ $scope.cuentasc = d; });
            });
        };

        $scope.getReembolso = function(idreembolso){
            $scope.resetCompra();
            $scope.detcontreem = [];
            reembolsoSrvc.getReembolso(idreembolso).then(function(d){
                $scope.reembolso = procDataReemb(d)[0];
                $scope.reembolso.objTipoReembolso = $filter('getById')($scope.tiposreembolso, $scope.reembolso.idtiporeembolso);
                $scope.reembolso.objBeneficiario = [$filter('getById')($scope.beneficiarios, $scope.reembolso.idbeneficiario)];
                $scope.reemstr = $scope.reembolso.tipo + ', No. ' + $filter('padNumber')(idreembolso, 5) + ', Iniciando el ' + moment($scope.reembolso.finicio).format('DD/MM/YYYY') + ', ' + $scope.reembolso.beneficiario;
                $scope.getDetReem($scope.reembolso.id);
                bancoSrvc.lstBancos($scope.reembolso.idempresa).then(function(d){ $scope.bancos = d; });
                tipoMovTranBanSrvc.getBySuma(0).then(function(d){ $scope.tiposmov = d; });
                reembolsoSrvc.getTranBan(idreembolso).then(function(d){ $scope.tranban = d; });
                detContSrvc.lstDetalleCont($scope.origenReembolsos, idreembolso).then(function(d){
                    for(var i = 0; i < d.length; i++){
                        d[i].debe = parseFloat(parseFloat(d[i].debe).toFixed(2));
                        d[i].haber = parseFloat(parseFloat(d[i].haber).toFixed(2));
                    }
                    $scope.detcontreem = d;
                });
                goTop();
            });
        };

        $scope.addReembolso = function(obj){
            //obj.idempresa = obj.idempresa;
            obj.finiciostr = moment(obj.finicio).format('YYYY-MM-DD');
            obj.ffinstr = !moment(obj.ffin).isValid() ? '' : moment(obj.ffin).format('YYYY-MM-DD');
            obj.idbeneficiario = obj.objBeneficiario[0].id;
            obj.tblbeneficiario = obj.tblbeneficiario != null && obj.tblbeneficiario != undefined ? obj.tblbeneficiario : '';
            obj.estatus = 1;
            obj.idtiporeembolso = obj.objTipoReembolso.id;
            reembolsoSrvc.editRow(obj, 'c').then(function(d){
                $scope.getLstReembolsos();
                $scope.getReembolso(parseInt(d.lastid));
            });
        };

        $scope.updReembolso = function(obj){
            obj.finiciostr = moment(obj.finicio).format('YYYY-MM-DD');
            obj.ffinstr = !moment(obj.ffin).isValid() ? '' : moment(obj.ffin).format('YYYY-MM-DD');
            obj.idbeneficiario = obj.objBeneficiario[0].id;
            obj.tblbeneficiario = obj.tblbeneficiario != null && obj.tblbeneficiario != undefined ? obj.tblbeneficiario : '';
            obj.idtiporeembolso = obj.objTipoReembolso.id;
            reembolsoSrvc.editRow(obj, 'u').then(function(){
                $scope.getLstReembolsos();
            });
        };

        $scope.delReembolso = function(obj){
            $confirm({text: '¿Seguro(a) de eliminar este reembolso? (También se eliminará su detalle)',
                title: 'Eliminar reembolso', ok: 'Sí', cancel: 'No'}).then(function() {
                reembolsoSrvc.editRow({id: obj.id, origen: $scope.origen}, 'd').then(function(){
                    $scope.getLstReembolsos();
                    $scope.resetReembolso();
                });
            });
        };

        function esCombustible(){
            if($scope.compra.objTipoCompra != null && $scope.compra.objTipoCompra != undefined){
                if($scope.compra.objTipoCompra.id != null && $scope.compra.objTipoCompra.id != undefined){
                    if(parseInt($scope.compra.objTipoCompra.id) == 3){
                        return true;
                    }
                }

            }
            return false;
        }

        function calcIDP(genidp){
            if(genidp){
                var galones = $scope.compra.galones != null && $scope.compra.galones != undefined ? parseFloat($scope.compra.galones) : 0.00;
                var impuesto = $scope.compra.objTipoCombustible.impuesto != null && $scope.compra.objTipoCombustible.impuesto != undefined ? parseFloat($scope.compra.objTipoCombustible.impuesto) : 0.00;
                //console.log(galones); console.log(impuesto); console.log((galones * impuesto).toFixed(2));
                return (galones * impuesto).toFixed(2);
            }
            return 0.00;
        }

        $scope.calcIVA = function(obj){
            var total = 0.00, noafecto = 0.00, subtotal = 0.00, genidp = esCombustible(), idp = 0.00;
            $scope.compra.idp = calcIDP(genidp);
            if(obj.objTipoFactura.generaiva && obj.totfact != null && obj.totfact != undefined){
                total = parseFloat(parseFloat($scope.compra.totfact).toFixed(2));
                noafecto = parseFloat(parseFloat($scope.compra.noafecto).toFixed(2));
                idp = parseFloat(parseFloat($scope.compra.idp).toFixed(2));
                subtotal = parseFloat((((total - noafecto) / 1.12) + noafecto).toFixed(2));
                $scope.compra.subtotal = subtotal;
                $scope.compra.iva = parseFloat((total - subtotal).toFixed(2));
            }else{
                total = parseFloat(parseFloat($scope.compra.totfact).toFixed(2));
                noafecto = parseFloat(parseFloat($scope.compra.noafecto).toFixed(2));
                idp = parseFloat(parseFloat($scope.compra.idp).toFixed(2));
                subtotal = total;
                $scope.compra.subtotal = subtotal;
                $scope.compra.iva = 0.00;
            }
        };

        $scope.nitSelected = function(item){
            $scope.compra.nit = item.originalObject.nit;
            $scope.compra.proveedor = item.originalObject.proveedor;
        };

        $scope.$watch('compra.proveedor', function(newValue, oldValue){
            if(newValue != null && newValue != undefined){
                $scope.compra.proveedor = newValue.toUpperCase();
            }
        });

        $scope.getCompra = function(obj, evento, subir){
            reembolsoSrvc.getCompra(obj.id).then(function(d){
                $scope.compra = procDataCompras(d)[0];
                $scope.compra.objTipoFactura = $filter('getById')($scope.tiposfactura, $scope.compra.idtipofactura);
                $scope.compra.objTipoCompra = $filter('getById')($scope.tiposcompra, $scope.compra.idtipocompra);
                $scope.compra.objTipoCombustible = $filter('getById')($scope.combustibles, $scope.compra.idtipocombustible);
                $scope.$broadcast('angucomplete-alt:changeInput', 'txtNit', {nit: $scope.compra.nit, proveedor: $scope.compra.proveedor});
                $scope.comprastr = $scope.compra.proveedor + ', ' + $scope.compra.serie + ' '
                    + $scope.compra.documento + ', ' + $scope.compra.simbolo + ' ' + $scope.compra.totfact;
                if(!subir){ goTop(); }
            });
        };

        $scope.addCompra = function(obj){
            obj.idreembolso = parseInt($scope.reembolso.id);
            obj.idtipofactura = parseInt(obj.objTipoFactura.id);
            obj.fechaingresostr = moment(obj.fechaingreso).format('YYYY-MM-DD');
            obj.mesiva = moment(obj.fechaingreso).month() + 1;
            obj.fechafacturastr = moment(obj.fechafactura).format('YYYY-MM-DD');
            obj.idtipocompra = parseInt(obj.objTipoCompra.id);
            obj.idtipocombustible = (obj.objTipoCombustible != null && obj.objTipoCombustible != undefined) ? ((obj.objTipoCombustible.id != null && obj.objTipoCombustible.id != undefined) ? +obj.objTipoCombustible.id : 0) : 0;
            reembolsoSrvc.editRow(obj, 'cd').then(function(d){
                $scope.getDetReem($scope.reembolso.id);
                $scope.getCompra({id: parseInt(d.lastid)});
            });
        };

        $scope.updCompra = function(obj){
            obj.idtipofactura = parseInt(obj.objTipoFactura.id);
            obj.fechaingresostr = moment(obj.fechaingreso).format('YYYY-MM-DD');
            obj.mesiva = moment(obj.fechaingreso).month() + 1;
            obj.fechafacturastr = moment(obj.fechafactura).format('YYYY-MM-DD');
            obj.idtipocompra = parseInt(obj.objTipoCompra.id);
            obj.idtipocombustible = (obj.objTipoCombustible != null && obj.objTipoCombustible != undefined) ? ((obj.objTipoCombustible.id != null && obj.objTipoCombustible.id != undefined) ? +obj.objTipoCombustible.id : 0) : 0;
            reembolsoSrvc.editRow(obj, 'ud').then(function(){ $scope.getDetReem($scope.reembolso.id); });
        };

        $scope.delCompra = function(obj){
            $confirm({text: '¿Seguro(a) de eliminar esta compra? (También se eliminará su detalle contable)',
                title: 'Eliminar compra', ok: 'Sí', cancel: 'No'}).then(function() {
                reembolsoSrvc.editRow({id: obj.id, origen: $scope.origen}, 'dd').then(function(){
                    $scope.getDetReem($scope.reembolso.id);
                    $scope.resetCompra();
                });
            });
        };

        $scope.selectCuentaC = function(busqueda){ $scope.detcont.objCuenta = busqueda == '' ? null : $filter('getByCodCta')($scope.cuentasc, busqueda); };

        $scope.zeroDebe = function(valor){ $scope.detcont.debe = parseFloat(valor) > 0 ? 0.0 : $scope.detcont.debe; };

        $scope.zeroHaber = function(valor){
            valor = parseFloat(parseFloat(valor).toFixed(2));
            var total = parseFloat(parseFloat($scope.compra.totfact).toFixed(2));
            var iva = parseFloat(parseFloat($scope.compra.iva).toFixed(2));
            var subtot = parseFloat((total - iva).toFixed(2));
            //console.log('Debe = ' + valor + '; Subtotal = ' + subtot);
            //$scope.detcont.debe = subtot;
            if(valor > subtot){
                toaster.pop({ type: 'error', title: 'Error en el monto del debe.',
                    body: 'El monto no puede ser mayor a ' + $filter('number')(subtot, 2) + ' de la factura.', timeout: 7000 });
                $scope.detcont.debe = null;
            }
            $scope.detcont.haber = parseFloat(valor) > 0 ? 0.0 : $scope.detcont.haber;
        };

        $scope.addDetCont = function(obj){
            obj.idorigen = $scope.compra.id;
            obj.origen = $scope.origen;
            obj.idcuenta = obj.objCuenta.id;
            obj.activada = 0;
            //console.log(obj); return;
            detContSrvc.editRow(obj, 'c').then(function(){
                $scope.rowFacturaExpanded({ id: obj.idorigen });
                $scope.detcont = {debe:0.00, haber:0.00};
                detContSrvc.estaCuadrada($scope.origen, +$scope.compra.id).then(function(d){ $scope.partidaCuadrada = +d.cuadrada; });
            });
        };

        $scope.delDetCont = function(obj){
            //console.log(obj); return;
            $confirm({text: '¿Seguro(a) de eliminar esta cuenta?',
                title: 'Eliminar cuenta', ok: 'Sí', cancel: 'No'}).then(function() {
                detContSrvc.editRow({id: obj.id}, 'd').then(function(){
                    $scope.rowFacturaExpanded({ id: obj.idorigen });
                    $scope.detcont = {debe:0.00, haber:0.00};
                    detContSrvc.estaCuadrada($scope.origen, +$scope.compra.id).then(function(d){ $scope.partidaCuadrada = +d.cuadrada; });
                });
            });
        };
    
        $scope.$on('$locationChangeStart', function(event){
            if(+$scope.partidaCuadrada < 0){
                if(!confirm('La partida contable no esta cuadrada o no tiene partida contable. ¿Desea continuar así?')){
                    event.preventDefault();
                }
            }
        });

        $scope.rowFacturaExpanded = function(obj){
            $scope.getCompra({ id: obj.id }, null, true);
            detContSrvc.lstDetalleCont($scope.origen, obj.id).then(function(d){
                var indice = 0;
                for(var i = 0; i < $scope.compras.length; i++){
                    if(parseInt($scope.compras[i].id) == parseInt(obj.id)){
                        indice = i;
                        break;
                    }
                }
                $scope.compras[i].detcont = d;
                $scope.detcont = {debe:0.00, haber:0.00};
            });
        };

        $scope.closeReembolso = function(obj){
            //console.log(obj);
            var modalInstance = $uibModal.open({
                animation: true,
                templateUrl: 'modalCierreReembolso.html',
                controller: 'ModalCierreReembolsoCtrl',
                resolve:{
                    fecIni: function(){return obj.finicio}
                }
            });

            modalInstance.result.then(function(fechacierre){
                obj.ffinstr = moment(fechacierre).format('YYYY-MM-DD');
                //console.log(obj); return;
                reembolsoSrvc.editRow(obj, 'cierre').then(function(){ $scope.getReembolso(obj.id); });
            }, function(){ return 0; });
        };

        $scope.genChequeReembolso = function(obj){
            //console.log(obj);
            var modalInstance = $uibModal.open({
                animation: true,
                templateUrl: 'modalGenChequeReembolso.html',
                controller: 'ModalGenChequeReembolsoCtrl',
                resolve:{
                    lsttipotrans: function(){ return $scope.tiposmov; },
                    lstbancos: function(){ return $scope.bancos; }
                }
            });

            modalInstance.result.then(function(seleccionados){
                obj.objBanco = seleccionados[0];
                obj.tipotrans = seleccionados[1].abreviatura;
                obj.numero = seleccionados[2].numero;
                obj.fechatrans = seleccionados[2].fechatrans;
                //console.log(obj); return;
                reembolsoSrvc.editRow(obj, 'gentranban').then(function(){ $scope.getReembolso(obj.id); });
            }, function(){ return 0; });
        };

        $scope.printVersion = function(){
            reembolsoSrvc.toPrint($scope.reembolso.id).then(function(d){
                $scope.dataToPrint = d;
                $scope.total = {debe: 0.00, haber: 0.00};
                var tmp = [];
                for(var i = 0; i < d.length; i++){
                    tmp = d[i].detcont;
                    for(var j = 0; j < tmp.length; j++){
                        if(tmp[j].desccuentacont.toUpperCase().indexOf('TOTAL --->') == -1){
                            $scope.total.debe += parseFloat(tmp[j].debe);
                            $scope.total.haber += parseFloat(tmp[j].haber);
                        }
                    }
                }
                $scope.total.debe = parseFloat($scope.total.debe.toFixed(2));
                $scope.total.haber = parseFloat($scope.total.haber.toFixed(2));
                PrintElem('#toPrint', 'Reembolso');
            });
        };

        $scope.comprasColDef = [
            {
                columnHeaderDisplayName: 'Tipo',
                displayProperty: 'tipofactura',
                sortKey: 'tipofactura'
            },
            {
                columnHeaderDisplayName: 'Proveedor',
                displayProperty: 'proveedor',
                sortKey: 'proveedor'
            },
            {
                columnHeaderDisplayName: 'N.I.T.',
                template: "<div class='text-right'>{{item.nit}}</div>",
                sortKey: 'nit'
            },
            {
                columnHeaderDisplayName: 'Documento',
                template: "<div class='text-right'>{{item.serie}}&nbsp;{{item.documento}}</div>",
                sortKey: 'documento'
            },
            {
                columnHeaderDisplayName: 'Fecha de factura',
                template: "{{item.fechafactura | date:'dd/MM/yyyy'}}",
                sortKey: 'fechaingreso'
            },
            {
                columnHeaderDisplayName: 'Tipo de compra',
                displayProperty: 'tipocompra',
                sortKey: 'tipocompra'
            },
            {
                columnHeaderDisplayName: 'Total',
                template: "<div class='text-right'>{{item.simbolo}}&nbsp;{{item.totfact | number:2}}</div>",
                sortKey: 'totfact'
            }

        ];
    }]);

    //------------------------------------------------------------------------------------------------------------------------------------------------//
    reembolsoctrl.controller('ModalCierreReembolsoCtrl', ['$scope', '$uibModalInstance', 'toaster', 'fecIni', function($scope, $uibModalInstance, toaster, fecIni){
        $scope.fcierre = moment().toDate();

        $scope.ok = function () {
            if(moment($scope.fcierre).isValid() && moment($scope.fcierre).isAfter(fecIni)){
                $uibModalInstance.close($scope.fcierre);
            }else{
                toaster.pop({ type: 'error', title: 'Error en la fecha de cierre.', body: 'Favor seleccionar una fecha de cierre válida', timeout: 7000 });
            }
        };

        $scope.cancel = function () {
            $uibModalInstance.dismiss('cancel');
        };
    }]);

    //------------------------------------------------------------------------------------------------------------------------------------------------//
    reembolsoctrl.controller('ModalGenChequeReembolsoCtrl', ['$scope', '$uibModalInstance', 'toaster', 'bancoSrvc', 'lsttipotrans', 'lstbancos', function($scope, $uibModalInstance, toaster, bancoSrvc, lsttipotrans, lstbancos){
        $scope.tipostrans = lsttipotrans;
        $scope.bancos = lstbancos;
        $scope.numerotranban = 0;
        $scope.fechatrans = moment().toDate();
        $scope.selectedBanco = {};
        $scope.selectedTipoMov = {};

        $scope.resetParams = function(){
            $scope.selectedTipoMov = {};
            $scope.numerotranban = 0;
            $scope.fechatrans = moment().toDate();
        };

        $scope.getNumCheque = function(tipoTran){
            if($scope.selectedBanco.id != null && $scope.selectedBanco.id != undefined){
                if($scope.selectedTipoMov.abreviatura === 'C'){
                    bancoSrvc.getCorrelativoBco(parseInt($scope.selectedBanco.id)).then(function(c){ $scope.numerotranban = parseInt(c[0].correlativo)});
                    $scope.revisaExistencia();
                }else{
                    $scope.numerotranban = 0;
                }
            }
        };

        $scope.revisaExistencia = function(){
            if($scope.selectedBanco.id != null && $scope.selectedBanco.id != undefined){
                if($scope.selectedTipoMov.abreviatura != null && $scope.selectedTipoMov.abreviatura != undefined){
                    bancoSrvc.checkTranExists(parseInt($scope.selectedBanco.id), $scope.selectedTipoMov.abreviatura, $scope.numerotranban).then(function(e){
                        var existe = parseInt(e.existe) == 1;
                        if(existe){
                            toaster.pop({ type: 'error', title: 'Cheque existente.',
                                body: 'La transacción No. ' + $scope.numerotranban + ' ya existe en el banco ' + $scope.selectedBanco.bancomoneda + '.',
                                timeout: 7000
                            });
                            $scope.numerotranban = 0;
                        }
                    });
                }else{
                    $scope.numerotranban = 0;
                }
            }
        };

        $scope.ok = function () {
            $scope.seleccionados = [];
            $scope.seleccionados.push($scope.selectedBanco);
            $scope.seleccionados.push($scope.selectedTipoMov);
            $scope.seleccionados.push({ numero: parseInt($scope.numerotranban), fechatrans: moment($scope.fechatrans).format('YYYY-MM-DD') });

            if($scope.selectedTipoMov.abreviatura == 'B'){
                if(parseInt($scope.numerotranban) > 0){
                    $uibModalInstance.close($scope.seleccionados);
                }else{
                    toaster.pop({ type: 'error', title: 'Datos insuficientes.', body: 'Favor ingresar el número de la nota de débito.', timeout: 7000 });
                }
            }else{
                $uibModalInstance.close($scope.seleccionados);
            }
        };

        $scope.cancel = function () {
            $uibModalInstance.dismiss('cancel');
        };
    }]);


}());
