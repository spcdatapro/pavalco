(function(){

    var compractrl = angular.module('cpm.compractrl', ['cpm.comprasrvc']);

    compractrl.controller('compraCtrl', ['$scope', '$filter', 'compraSrvc', 'authSrvc', 'empresaSrvc', 'DTOptionsBuilder', 'proveedorSrvc', 'tipoCompraSrvc', 'toaster', 'cuentacSrvc', 'detContSrvc', '$uibModal', '$confirm', 'monedaSrvc', 'tipoFacturaSrvc', 'tipoCombustibleSrvc', function($scope, $filter, compraSrvc, authSrvc, empresaSrvc, DTOptionsBuilder, proveedorSrvc, tipoCompraSrvc, toaster, cuentacSrvc, detContSrvc, $uibModal, $confirm, monedaSrvc, tipoFacturaSrvc, tipoCombustibleSrvc){

        $scope.lasEmpresas = [];
        $scope.lasCompras = [];
        var hoy = new Date();
        $scope.laCompra = {galones: 0.00, idp: 0.00};
        $scope.editando = false;
        $scope.losProvs = [];
        $scope.losTiposCompra = [];
        $scope.losDetCont = [];
        $scope.elDetCont = {debe: 0.0, haber: 0.0};
        $scope.lasCtasMov = [];
        $scope.origen = 2;
        $scope.ctasGastoProv = [];
        $scope.yaPagada = false;
        $scope.tranpago = [];
        $scope.monedas = [];
        $scope.dectc = 2;
        $scope.lsttiposfact = [];
        $scope.combustibles = [];
        $scope.partidaCuadrada = -1;
        $scope.conconta = false;

        $scope.dtOptions = DTOptionsBuilder.newOptions().withPaginationType('full_numbers').withBootstrap().withOption('responsive', true).withOption('fnRowCallback', rowCallback);

        empresaSrvc.lstEmpresas().then(function(d){ $scope.lasEmpresas = d; });
        tipoFacturaSrvc.lstTiposFactura().then(function(d){
            for(var i = 0; i < d.length; i++){ d[i].id = parseInt(d[i].id); d[i].paracompra = parseInt(d[i].paracompra); }
            $scope.lsttiposfact = d;
        });
        tipoCombustibleSrvc.lstTiposCombustible().then(function(d){
            for(var i = 0; i < d.length; i++){
                d[i].id = parseInt(d[i].id);
                d[i].impuesto = parseFloat(parseFloat(d[i].impuesto).toFixed(2));
            }
            $scope.combustibles = d;
        });

        authSrvc.getSession().then(function(usrLogged){
            if(parseInt(usrLogged.workingon) > 0){
                empresaSrvc.getEmpresa(parseInt(usrLogged.workingon)).then(function(d){
                    $scope.laCompra.objEmpresa = d[0];
                    $scope.dectc = parseInt(d[0].dectc);
                    monedaSrvc.lstMonedas().then(function(l){
                        $scope.monedas = l;
                        $scope.resetCompra();
                    });
                    empresaSrvc.conConta($scope.laCompra.objEmpresa.id).then(function(d){ $scope.conconta = d; });
                });
            }
        });

        proveedorSrvc.lstProveedores().then(function(d){ $scope.losProvs = d; });
        tipoCompraSrvc.lstTiposCompra().then(function(d){
            for(var i = 0; i < d.length; i++){ d[i].id = parseInt(d[i].id); }
            $scope.losTiposCompra = d;
        });

        $scope.resetCompra = function(){
            $scope.laCompra = {
                fechaingreso: new Date(), mesiva: hoy.getMonth() + 1, fechafactura: new Date(), creditofiscal: 0, extraordinario: 0, noafecto: 0.0,
                objEmpresa: $scope.laCompra.objEmpresa, objMoneda: {}, tipocambio: 1, isr: 0.00, galones: 0.00, idp: 0.00, objTipoCombustible: {},
                totfact: 0.00, subtotal: 0.00, iva: 0.00
            };
            $scope.search = "";
            $scope.losDetCont = [];
            $scope.tranpago = [];
            $scope.yaPagada = false;
            $scope.editando = false;
            monedaSrvc.getMoneda(parseInt($scope.laCompra.objEmpresa.idmoneda)).then(function(m){
                $scope.laCompra.objMoneda = m[0];
                $scope.laCompra.tipocambio = parseFloat(m[0].tipocambio).toFixed($scope.dectc);
            });
            goTop();
        };

        $scope.chkFecha = function(qFecha, cual){
            if(qFecha != null && qFecha != undefined){
                switch(cual){
                    case 1:
                        $scope.laCompra.mesiva = moment(qFecha).month() + 1;
                        if($scope.laCompra.objProveedor != null && $scope.laCompra.objProveedor != undefined){
                            $scope.laCompra.fechapago = moment(qFecha).add(parseInt($scope.laCompra.objProveedor.diascred), 'days').toDate();
                        }
                        break;
                    case 2:
                        var transcurrido = moment($scope.laCompra.fechaingreso).diff(moment(qFecha), 'months');
                        if(+transcurrido >= 2){
                            $confirm({text: 'La fecha de ingreso es dos meses luego de la fecha de la factura. ¿Desea continuar?', title: 'Fecha de ingreso posterior', ok: 'Sí', cancel: 'No'}).then(
                                function() {}, function(){
                                    $scope.laCompra.fechaingreso = undefined;
                                }
                            );
                        }
                        break;
                }
            }
        };

        function esCombustible(){
            if($scope.laCompra.objTipoCompra != null && $scope.laCompra.objTipoCompra != undefined){
                if($scope.laCompra.objTipoCompra.id != null && $scope.laCompra.objTipoCompra.id != undefined){
                    if(parseInt($scope.laCompra.objTipoCompra.id) == 3){
                        return true;
                    }
                }

            }
            return false;
        }

        function calcIDP(genidp){
            if(genidp){
                var galones = $scope.laCompra.galones != null && $scope.laCompra.galones != undefined ? parseFloat($scope.laCompra.galones) : 0.00;
                var impuesto = $scope.laCompra.objTipoCombustible.impuesto != null && $scope.laCompra.objTipoCombustible.impuesto != undefined ? parseFloat($scope.laCompra.objTipoCombustible.impuesto) : 0.00;
                //console.log(galones); console.log(impuesto); console.log((galones * impuesto).toFixed(2));
                return (galones * impuesto).toFixed(2);
            }
            return 0.00;
        }

        $scope.calcular = function(){
            var geniva = true;
            var genidp = esCombustible();
            var totFact = $scope.laCompra.totfact != null && $scope.laCompra.totfact != undefined ? parseFloat($scope.laCompra.totfact) : 0;
            var noAfecto = $scope.laCompra.noafecto != null && $scope.laCompra.noafecto != undefined ? parseFloat($scope.laCompra.noafecto) : 0;

            if($scope.laCompra.objTipoFactura != null && $scope.laCompra.objTipoFactura != undefined){ geniva = parseInt($scope.laCompra.objTipoFactura.generaiva) === 1; }
            //if($scope.laCompra.objTipoCompra != null && $scope.laCompra.objTipoCompra != undefined){ genidp = $scope.laCompra.objTipoCompra.id === 3; }

            $scope.laCompra.idp = calcIDP(genidp);

            //$scope.laCompra.idp = $scope.laCompra.idp.toFixed(2);

            if(noAfecto <= totFact){
                $scope.laCompra.subtotal = geniva ? parseFloat((((totFact - noAfecto) / 1.12) + noAfecto).toFixed(2)) : totFact;
                $scope.laCompra.iva = geniva ? parseFloat((totFact - $scope.laCompra.subtotal).toFixed(2)) : 0.00;
            }else{
                $scope.laCompra.noafecto = 0;
                toaster.pop({ type: 'error', title: 'Error en el monto de No afecto.',
                    body: 'El monto de No afecto no puede ser mayor al total de la factura.', timeout: 7000 });
            }
        };

        $scope.$watch('laCompra.objEmpresa', function(newValue, oldValue){
            if(newValue != null && newValue != undefined){
                $scope.getLstCompras();
            }
        });

        $scope.getConcepto = function(qProv){
            //console.log(qProv); return;
            if(!$scope.laCompra.id > 0){
                if(qProv != null && qProv != undefined){
                    $scope.laCompra.conceptomayor = qProv.concepto;
                    $scope.laCompra.fechapago = moment($scope.laCompra.fechaingreso).add(parseInt(qProv.diascred), 'days').toDate();
                    $scope.laCompra.objMoneda = $filter('getById')($scope.monedas, parseInt(qProv.idmoneda));
                    $scope.laCompra.tipocambio = parseFloat(qProv.tipocambioprov).toFixed($scope.dectc);
                }
            }
        };

        $scope.setTipoCambio = function(qmoneda){
            if($scope.laCompra.id > 0 || ($scope.laCompra.objProveedor != null && $scope.laCompra.objProveedor != undefined)){
                if(parseInt(qmoneda.id) === parseInt($scope.laCompra.objProveedor.idmoneda)){
                    $scope.laCompra.tipocambio = parseFloat($scope.laCompra.objProveedor.tipocambioprov);
                }else{
                    $scope.laCompra.tipocambio = parseFloat(qmoneda.tipocambio).toFixed($scope.dectc);
                }
            }else{ $scope.laCompra.tipocambio = parseFloat(qmoneda.tipocambio).toFixed($scope.dectc); }
        };

        function dateToStr(fecha){ return fecha !== null && fecha !== undefined ? (fecha.getFullYear() + '-' + (fecha.getMonth() + 1) + '-' + fecha.getDate()) : ''; }

        function procDataCompras(data){
            for(var i = 0; i < data.length; i++){
                data[i].documento = parseInt(data[i].documento);
                data[i].mesiva = parseInt(data[i].mesiva);
                data[i].ordentrabajo = parseInt(data[i].ordentrabajo);
                data[i].totfact = parseFloat(parseFloat(data[i].totfact).toFixed(2));
                data[i].noafecto = parseFloat(parseFloat(data[i].noafecto).toFixed(2));
                data[i].subtotal = parseFloat(parseFloat(data[i].subtotal).toFixed(2));
                data[i].iva = parseFloat(parseFloat(data[i].iva).toFixed(2));
                data[i].isr = parseFloat(parseFloat(data[i].isr).toFixed(2));
                data[i].fechaingreso = moment(data[i].fechaingreso).toDate();
                data[i].fechafactura = moment(data[i].fechafactura).toDate();
                data[i].fechapago = moment(data[i].fechapago).toDate();
                data[i].creditofiscal = parseInt(data[i].creditofiscal);
                data[i].extraordinario = parseInt(data[i].extraordinario);
                data[i].idproveedor = parseInt(data[i].idproveedor);
                data[i].idtipocompra = parseInt(data[i].idtipocompra);
                data[i].cantpagos = parseInt(data[i].cantpagos);
                data[i].idmoneda = parseInt(data[i].idmoneda);
                data[i].tipocambio = parseFloat(parseFloat(data[i].tipocambio).toFixed($scope.dectc));
                data[i].idtipofactura = parseInt(data[i].idtipofactura);
                data[i].idtipocombustible = parseInt(data[i].idtipocombustible);
                data[i].galones = parseFloat(parseFloat(data[i].galones).toFixed(2));
                data[i].galones = parseFloat(parseFloat(data[i].galones).toFixed(2));
                data[i].idp = parseFloat(parseFloat(data[i].idp).toFixed(2));
                data[i].fecpagoformisr = moment(data[i].fecpagoformisr).isValid() ? moment(data[i].fecpagoformisr).toDate() : null;
            }
            return data;
        }

        function procDataDet(data){
            for(var i = 0; i < data.length; i++){
                data[i].debe = parseFloat(data[i].debe);
                data[i].haber = parseFloat(data[i].haber);
            }
            return data;
        }

        $scope.getLstCompras = function(){
            compraSrvc.lstCompras(parseInt($scope.laCompra.objEmpresa.id)).then(function(d){
                $scope.lasCompras = procDataCompras(d);
            });
        };

        $scope.getDetCont = function(idcomp){
            detContSrvc.lstDetalleCont($scope.origen, parseInt(idcomp)).then(function(detc){
                $scope.losDetCont = procDataDet(detc);
                detContSrvc.estaCuadrada($scope.origen, +idcomp).then(function(d){ $scope.partidaCuadrada = +d.cuadrada; });
            });
        };

        $scope.modalISR = function(){
            var modalInstance = $uibModal.open({
                animation: true,
                templateUrl: 'modalISR.html',
                controller: 'ModalISR',
                resolve:{
                    compra: function(){
                        return $scope.laCompra;
                    }
                }
            });

            modalInstance.result.then(function(idcompra){
                $scope.getCompra(parseInt(idcompra));
            }, function(){ return 0; });
        };

        $scope.getCompra = function(idcomp){
            compraSrvc.getCompra(idcomp).then(function(d){
                $scope.laCompra = procDataCompras(d)[0];
                $scope.laCompra.objProveedor = $filter('getById')($scope.losProvs, $scope.laCompra.idproveedor);
                $scope.laCompra.objMoneda = $filter('getById')($scope.monedas, $scope.laCompra.idmoneda);
                $scope.laCompra.objTipoFactura = $filter('getById')($scope.lsttiposfact, $scope.laCompra.idtipofactura);
                $scope.laCompra.objTipoCombustible = $filter('getById')($scope.combustibles, $scope.laCompra.idtipocombustible);
                $scope.search = $scope.laCompra.objProveedor.nitnombre;
                tipoCompraSrvc.getTipoCompra($scope.laCompra.idtipocompra).then(function(tc){ $scope.laCompra.objTipoCompra = tc[0]; });
                $scope.editando = true;
                cuentacSrvc.getByTipo($scope.laCompra.idempresa, 0).then(function(d){ $scope.lasCtasMov = d; });
                $scope.getDetCont(idcomp);
                empresaSrvc.getEmpresa(parseInt($scope.laCompra.idempresa)).then(function(d){ $scope.laCompra.objEmpresa = d[0]; });
                compraSrvc.getTransPago(idcomp).then(function(d){
                    for(var i = 0; i < d.length; i++){
                        d[i].idtranban = parseInt(d[i].idtranban);
                        d[i].numero = parseInt(d[i].numero);
                        d[i].monto = parseFloat(d[i].monto);
                    }
                    $scope.tranpago = d;
                    $scope.yaPagada = $scope.tranpago.length > 0;
                });

                if($scope.laCompra.isr > 0){
                    if($scope.laCompra.noformisr == '' || $scope.laCompra.noformisr == undefined || $scope.laCompra.noformisr == null){
                        $scope.modalISR();
                    }
                }

                goTop();
            });
        };

        function execCreate(obj) {
            compraSrvc.editRow(obj,'c').then(function(d){
                $scope.getLstCompras();
                $scope.getCompra(parseInt(d.lastid));
            });
        }

        function execUpdate(obj) {
            //console.log(obj);
            compraSrvc.editRow(obj,'u').then(function(d){
                $scope.getLstCompras();
                $scope.getCompra(parseInt(d.lastid));
            });
        }

        $scope.openSelectCtaGastoProv = function(obj, op){
            var modalInstance = $uibModal.open({
                animation: true,
                templateUrl: 'modalSelectCtaGastoProv.html',
                controller: 'ModalCtasGastoProvCtrl',
                resolve:{
                    lstctasgasto: function(){
                        return $scope.ctasGastoProv;
                    }
                }
            });

            modalInstance.result.then(function(selectedItem){
                obj.ctagastoprov = selectedItem.idcuentac;
                switch (op){
                    case 'c': execCreate(obj); break;
                    case 'u': execUpdate(obj); break;
                }
            }, function(){ return 0; });
        };

        $scope.addCompra = function(obj){
            obj.idempresa = parseInt(obj.objEmpresa.id);
            obj.idproveedor = parseInt(obj.objProveedor.id);
            obj.conceptoprov = obj.objProveedor.concepto;
            obj.idtipocompra = parseInt(obj.objTipoCompra.id);
            obj.creditofiscal = obj.creditofiscal != null && obj.creditofiscal != undefined ? obj.creditofiscal : 0;
            obj.extraordinario = obj.extraordinario != null && obj.extraordinario != undefined ? obj.extraordinario : 0;
            obj.ordentrabajo = obj.ordentrabajo != null && obj.ordentrabajo != undefined ? obj.ordentrabajo : 0;
            obj.fechaingresostr = dateToStr(obj.fechaingreso);
            obj.fechafacturastr = dateToStr(obj.fechafactura);
            obj.fechapagostr = dateToStr(obj.fechapago);
            obj.idmoneda = parseInt(obj.objMoneda.id);
            obj.idtipofactura = parseInt(obj.objTipoFactura.id);
            obj.idtipocombustible = obj.objTipoCombustible.id != null && obj.objTipoCombustible.id != undefined ? obj.objTipoCombustible.id : 0;
            obj.mesiva = moment(obj.fechaingreso).month() + 1;
            //obj.idtipocombustible = 0;

            proveedorSrvc.getLstCuentasCont(obj.idproveedor).then(function(lstCtas){
                $scope.ctasGastoProv = lstCtas;
                switch(true){
                    case $scope.ctasGastoProv.length == 0:
                        obj.ctagastoprov = 0;
                        //console.log(obj);
                        execCreate(obj);
                        break;
                    case $scope.ctasGastoProv.length == 1:
                        obj.ctagastoprov = parseInt($scope.ctasGastoProv[0].idcuentac);
                        //console.log(obj);
                        execCreate(obj);
                        break;
                    case $scope.ctasGastoProv.length > 1:
                        $scope.openSelectCtaGastoProv(obj, 'c');
                        break;
                }
            });
        };

        $scope.updCompra = function(obj){
            obj.idempresa = parseInt(obj.idempresa);
            obj.idproveedor = parseInt(obj.objProveedor.id);
            obj.conceptoprov = obj.objProveedor.concepto;
            obj.idtipocompra = parseInt(obj.objTipoCompra.id);
            obj.creditofiscal = obj.creditofiscal != null && obj.creditofiscal != undefined ? obj.creditofiscal : 0;
            obj.extraordinario = obj.extraordinario != null && obj.extraordinario != undefined ? obj.extraordinario : 0;
            obj.ordentrabajo = obj.ordentrabajo != null && obj.ordentrabajo != undefined ? obj.ordentrabajo : 0;
            obj.fechaingresostr = dateToStr(obj.fechaingreso);
            obj.fechafacturastr = dateToStr(obj.fechafactura);
            obj.fechapagostr = dateToStr(obj.fechapago);
            obj.idmoneda = parseInt(obj.objMoneda.id);
            obj.idtipofactura = parseInt(obj.objTipoFactura.id);
            obj.idtipocombustible = obj.objTipoCombustible != null && obj.objTipoCombustible != undefined ? (obj.objTipoCombustible.id != null && obj.objTipoCombustible.id != undefined ? obj.objTipoCombustible.id : 0) : 0;
            obj.mesiva = moment(obj.fechaingreso).month() + 1;
            //obj.idtipocombustible = 0;

            proveedorSrvc.getLstCuentasCont(obj.idproveedor).then(function(lstCtas){
                $scope.ctasGastoProv = lstCtas;
                switch(true){
                    case $scope.ctasGastoProv.length == 0:
                        obj.ctagastoprov = 0;
                        //console.log(obj);
                        execUpdate(obj);
                        break;
                    case $scope.ctasGastoProv.length == 1:
                        obj.ctagastoprov = parseInt($scope.ctasGastoProv[0].idcuentac);
                        //console.log(obj);
                        execUpdate(obj);
                        break;
                    case $scope.ctasGastoProv.length > 1:
                        $scope.openSelectCtaGastoProv(obj, 'u');
                        break;
                }
            });

            //$confirm({text: 'Este proceso eliminará el detalle contable que ya se haya ingresado y se creará uno nuevo. ¿Seguro(a) de continuar?', title: 'Actualización de factura de compra', ok: 'Sí', cancel: 'No'}).then(function() { });
        };

        $scope.delCompra = function(obj){
            $confirm({text: '¿Seguro(a) de eliminar esta factura de compra?' + ($scope.conconta ? ' (También se eliminará su detalle contable)' : ''),
                title: 'Eliminar factura de compra', ok: 'Sí', cancel: 'No'}).then(function() {
                compraSrvc.editRow({id:obj.id}, 'd').then(function(){
                    $scope.getLstCompras();
                    $scope.resetCompra();
                });
            });
        };

        $scope.zeroDebe = function(valor){ $scope.elDetCont.debe = parseFloat(valor) > 0 ? 0.0 : $scope.elDetCont.debe; };
        $scope.zeroHaber = function(valor){ $scope.elDetCont.haber = parseFloat(valor) > 0 ? 0.0 : $scope.elDetCont.haber; };

        $scope.addDetCont = function(obj){
            obj.origen = $scope.origen;
            obj.idorigen = parseInt($scope.laCompra.id);
            obj.debe = parseFloat(obj.debe);
            obj.haber = parseFloat(obj.haber);
            obj.idcuenta = parseInt(obj.objCuenta[0].id);
            detContSrvc.editRow(obj, 'c').then(function(){
                detContSrvc.lstDetalleCont($scope.origen, parseInt($scope.laCompra.id)).then(function(detc){
                    $scope.losDetCont = procDataDet(detc);
                    $scope.elDetCont = {debe: 0.0, haber: 0.0};
                    $scope.searchcta = "";
                    detContSrvc.estaCuadrada($scope.origen, +$scope.laCompra.id).then(function(d){ $scope.partidaCuadrada = +d.cuadrada; });
                });
            });
        };

        $scope.delDetCont = function(obj){
            $confirm({text: '¿Seguro(a) de eliminar esta cuenta?', title: 'Eliminar cuenta contable', ok: 'Sí', cancel: 'No'}).then(function() {
                detContSrvc.editRow({id:obj.id}, 'd').then(function(){
                    $scope.getDetCont(obj.idorigen);
                    detContSrvc.estaCuadrada($scope.origen, +$scope.laCompra.id).then(function(d){ $scope.partidaCuadrada = +d.cuadrada; });
                });
            });
        };

        $scope.$on('$locationChangeStart', function(event){
            if(+$scope.partidaCuadrada < 0 && $scope.conconta){
                if(!confirm('La partida contable no esta cuadrada o no tiene partida contable. ¿Desea continuar así?')){
                    event.preventDefault();
                }
            }
        });

        $scope.printVersion = function(){ PrintElem('#toPrint', 'Factura de compra'); };

    }]);
    //------------------------------------------------------------------------------------------------------------------------------------------------//
    compractrl.controller('ModalCtasGastoProvCtrl', ['$scope', '$uibModalInstance', 'lstctasgasto', function($scope, $uibModalInstance, lstctasgasto){
        $scope.lasCtasGasto = lstctasgasto;
        $scope.selectedCta = [];

        $scope.ok = function () {
            $uibModalInstance.close($scope.selectedCta[0]);
        };

        $scope.cancel = function () {
            $uibModalInstance.dismiss('cancel');
        };
    }]);
    //------------------------------------------------------------------------------------------------------------------------------------------------//
    compractrl.controller('ModalISR', ['$scope', '$uibModalInstance', 'compra', 'compraSrvc', function($scope, $uibModalInstance, compra, compraSrvc){
        $scope.compra = compra;
        $scope.compra.isrlocal = parseFloat(($scope.compra.isr * $scope.compra.tipocambio).toFixed(2));
        //console.log($scope.compra);

        $scope.setMesAnio = function(){
            if(moment($scope.compra.fecpagoformisr).isValid()){
                $scope.compra.mesisr = moment($scope.compra.fecpagoformisr).month() + 1;
                $scope.compra.anioisr = moment($scope.compra.fecpagoformisr).year();
            }
        };

        $scope.ok = function () {
            $scope.compra.noformisr = $scope.compra.noformisr != null && $scope.compra.noformisr != undefined ? $scope.compra.noformisr : '';
            $scope.compra.noaccisr = $scope.compra.noaccisr != null && $scope.compra.noaccisr != undefined ? $scope.compra.noaccisr : '';
            $scope.compra.fecpagoformisrstr = moment($scope.compra.fecpagoformisr).isValid() ? moment($scope.compra.fecpagoformisr).format('YYYY-MM-DD') : '';
            $scope.compra.mesisr = $scope.compra.mesisr != null && $scope.compra.mesisr != undefined ? $scope.compra.mesisr : 0;
            $scope.compra.anioisr = $scope.compra.anioisr != null && $scope.compra.anioisr != undefined ? $scope.compra.anioisr : 0;
            compraSrvc.editRow($scope.compra, 'uisr').then(function(){ $uibModalInstance.close($scope.compra.id); });
        };

        $scope.cancel = function () {
            $uibModalInstance.dismiss('cancel');
        };

    }]);

}());
