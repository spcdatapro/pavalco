(function(){

    var tranbancctrl = angular.module('cpm.tranbancctrl', ['cpm.tranbacsrvc']);

    tranbancctrl.controller('tranBancCtrl', ['$scope', 'tranBancSrvc', 'authSrvc', 'bancoSrvc', 'empresaSrvc', 'DTOptionsBuilder', 'tipoDocSopTBSrvc', 'tipoMovTranBanSrvc', 'periodoContableSrvc', 'toaster', 'detContSrvc', 'cuentacSrvc', '$confirm', '$filter', '$uibModal', 'razonAnulacionSrvc', '$route', function($scope, tranBancSrvc, authSrvc, bancoSrvc, empresaSrvc, DTOptionsBuilder, tipoDocSopTBSrvc, tipoMovTranBanSrvc, periodoContableSrvc, toaster, detContSrvc, cuentacSrvc, $confirm, $filter, $uibModal, razonAnulacionSrvc, $route){

        $scope.laTran = {fecha: new Date(), concepto: '', anticipo: 0, idbeneficiario: 0, tipocambio: parseFloat('1.00').toFixed($scope.dectc), esajustedc: 0};
        $scope.laEmpresa = {};
        $scope.lasEmpresas = [];
        $scope.losBancos = [];
        $scope.lasTran = [];
        $scope.editando = false;
        $scope.strTran = '';
        $scope.losDocsSoporte = [];
        $scope.elDocSop = {fechadoc: moment().toDate(), fechaliquida: null};
        $scope.losTiposDocTB = [];
        $scope.origen = 1;
        $scope.losDetCont = [];
        $scope.elDetCont = {debe: 0.0, haber: 0.0};
        $scope.origenLiq = 9;
        $scope.liquidacion = [];
        $scope.lasCuentasMov = [];
        $scope.beneficiarios = [];
        $scope.compraspendientes = [];
        $scope.razonesanula = [];
        $scope.dectc = 2;
        $scope.permiso = {};
        $scope.conconta = false;

        $scope.tipotrans = [];
        $scope.dtOptions = DTOptionsBuilder.newOptions().withPaginationType('full_numbers').withBootstrap().withOption('responsive', true);
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

        $scope.dtOptionsDetContLiquidacion = DTOptionsBuilder.newOptions().withBootstrap()
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

        empresaSrvc.lstEmpresas().then(function(d){
            $scope.lasEmpresas = d;
        });

        tipoMovTranBanSrvc.lstTiposMovTB().then(function(d){ $scope.tipotrans = d; });
        tranBancSrvc.lstBeneficiarios().then(function(d){ $scope.beneficiarios = d; });
        razonAnulacionSrvc.lstRazones().then(function(d){$scope.razonesanula = d; });

        authSrvc.getSession().then(function(usrLogged){
            if(parseInt(usrLogged.workingon) > 0){
                authSrvc.gpr({idusuario: parseInt(usrLogged.uid), ruta:$route.current.params.name}).then(function(d){ $scope.permiso = d; });
                empresaSrvc.getEmpresa(parseInt(usrLogged.workingon)).then(function(d){
                    $scope.laEmpresa = d[0];
                    $scope.dectc = parseInt(d[0].dectc);
                    $scope.getLstBancos();
                    empresaSrvc.conConta($scope.laEmpresa.id).then(function(d){ $scope.conconta = d; });
                });
            }
        });

        $scope.$watch('laTran.fecha', function(newValue, oldValue){
            if(newValue != null && newValue != undefined){
                $scope.chkFechaEnPeriodo(newValue, 't');
            }
        });

        $scope.$watch('elDocSop.fechadoc', function(newValue, oldValue){
            if(newValue != null && newValue != undefined){
                $scope.chkFechaEnPeriodo(newValue, 'd');
            }
        });

        $scope.$watch('laTran.beneficiario', function(newValue, oldValue){
            if(newValue != null && newValue != undefined){
                $scope.laTran.beneficiario = newValue.toUpperCase();
            }
        });

        $scope.getLstBancos = function(){
            bancoSrvc.lstBancos(parseInt($scope.laEmpresa.id)).then(function(r){
                $scope.losBancos = r;
                $scope.lasTran = [];
            });
        };

        $scope.getLstTran = function(){
            if($scope.laTran.objBanco != null && $scope.laTran.objBanco != undefined){
                $scope.laTran.tipocambio = $scope.laTran.objBanco.tipocambio;
                tranBancSrvc.lstTransacciones($scope.laTran.objBanco.id).then(function(d){
                    $scope.lasTran = d;
                    for(var i = 0; i < $scope.lasTran.length; i++){
                        //$scope.lasTran[i].fecha = new Date($scope.lasTran[i].fecha);
                        $scope.lasTran[i].fecha = moment($scope.lasTran[i].fecha).toDate();
                        $scope.lasTran[i].numero = parseInt($scope.lasTran[i].numero);
                        $scope.lasTran[i].monto = parseFloat($scope.lasTran[i].monto);
                        $scope.lasTran[i].operado = parseInt($scope.lasTran[i].operado);
                        $scope.lasTran[i].anticipo = parseInt($scope.lasTran[i].anticipo);
                    }
                });
            }
        };

        $scope.resetLaTran = function(){
            $scope.laTran = {fecha: new Date(), concepto: '', anticipo: 0, idbeneficiario: 0, tipocambio: parseFloat('1.00').toFixed($scope.dectc)};
            $scope.lasTran = [];
            $scope.losDocsSoporte = [];
            $scope.elDocSop = {fechadoc: moment().toDate(), fechaliquida: null};
            $scope.losDetCont = [];
            $scope.elDetCont = {debe: 0.0, haber: 0.0};
            $scope.strTran = '';
            $scope.editando = false;
        };

        $scope.getNumCheque = function(){
            if($scope.laTran.objBanco.id != null && $scope.laTran.objBanco.id != undefined){
                if($scope.laTran.objTipotrans.abreviatura === 'C'){
                    bancoSrvc.getCorrelativoBco(parseInt($scope.laTran.objBanco.id)).then(function(c){ $scope.laTran.numero = parseInt(c[0].correlativo)});
                }else{
                    $scope.laTran.numero = 0;
                }
            }
        };

        $scope.chkFechaEnPeriodo = function(qFecha, deDonde){
            if(angular.isDate(qFecha)){
                if(qFecha.getFullYear() >= 2000){
                    //console.log(qFecha);
                    periodoContableSrvc.validaFecha(moment(qFecha).format('YYYY-MM-DD')).then(function(d){
                        var fechaValida = parseInt(d.valida) === 1;
                        if(!fechaValida){
                            var cualFecha = '';
                            var tipo = '';
                            switch(deDonde){
                                case 't' :
                                    $scope.laTran.fecha = null;
                                    cualFecha = 'de la transacción';
                                    tipo = 'error';
                                    break;
                                case 'd' :
                                    $scope.elDocSop.fechadoc = null;
                                    cualFecha = 'del documento de soporte';
                                    tipo = 'warning';
                                    break;
                            }
                            toaster.pop({ type:''+ tipo +'', title: 'Fecha '+ cualFecha +' es inválida.',
                                body: 'No está dentro de ningún período contable abierto.', timeout: 7000 });


                        }
                    });
                }
            }
        };

        $scope.setNombreBene = function(bene){ $scope.laTran.beneficiario = bene != null && bene != undefined ?  bene.chequesa : ''; };

        $scope.getDocs = function(td){
            switch(parseInt(td.id)){
                case 1: tranBancSrvc.lstFactCompra($scope.laTran.idbeneficiario, $scope.laTran.id).then(function(d){ $scope.compraspendientes = d; }); break;
                case 2: tranBancSrvc.lstReembolsos($scope.laTran.idbeneficiario).then(function(d){ $scope.compraspendientes = d; }); break;
            }
        };

        $scope.setData = function(ds){
            $scope.elDocSop.fechadoc = moment(ds.fechafactura).toDate();
            $scope.elDocSop.serie = ds.serie;
            $scope.elDocSop.documento = ds.documento;
            $scope.elDocSop.monto = parseFloat(ds.totfact);

            if(parseFloat($scope.laTran.monto) != parseFloat($scope.elDocSop.monto)){
                toaster.pop({
                    type: 'warning',
                    title: 'Advertencia.',
                    body: 'El monto de la transacción (' + parseFloat($scope.laTran.monto).toFixed(2) +
                    ') no cuadra con el monto del documento de soporte (' + parseFloat($scope.elDocSop.monto).toFixed(2) + ').',
                    timeout: 7000
                });
            }
        };

        $scope.addTran = function(obj){
            obj.idbanco = obj.objBanco.id;
            obj.fechastr = moment(obj.fecha).format('YYYY-MM-DD');
            obj.tipotrans = obj.objTipotrans.abreviatura;
            obj.anticipo = obj.anticipo != null && obj.anticipo != undefined ? obj.anticipo : 0;
            obj.idbeneficiario = (parseInt(obj.anticipo) === 0) ? 0 : (obj.objBeneficiario[0] != null && obj.objBeneficiario[0] != undefined ? obj.objBeneficiario[0].id : 0);
            obj.origenbene = (parseInt(obj.anticipo) === 0) ? 0 : (obj.objBeneficiario[0] != null && obj.objBeneficiario[0] != undefined ? obj.objBeneficiario[0].dedonde : 0);
            obj.esajustedc = obj.esajustedc != null && obj.esajustedc != undefined ? obj.esajustedc : 0;
            tranBancSrvc.editRow(obj, 'c').then(function(d){
                $scope.getLstTran();
                $scope.getDataTran(parseInt(d.lastid));
            });
        };

        function processData(data){
            for(var i = 0; i < data.length; i++){
                data[i].id = parseInt(data[i].id);
                data[i].idbanco = parseInt(data[i].idbanco);
                data[i].fecha = moment(data[i].fecha).toDate();
                data[i].numero = parseInt(data[i].numero);
                data[i].monto = parseFloat(parseFloat(data[i].monto).toFixed(2));
                data[i].operado = parseInt(data[i].operado);
                data[i].anticipo = parseInt(data[i].anticipo);
                data[i].idbeneficiario = parseInt(data[i].idbeneficiario);
                data[i].origenbene = parseInt(data[i].origenbene);
                data[i].esajustedc = parseInt(data[i].esajustedc);
                data[i].anulado = parseInt(data[i].anulado);
                data[i].fechaanula = moment(data[i].fechaanula).toDate();
                data[i].tipocambio = parseFloat(parseFloat(data[i].tipocambio).toFixed($scope.dectc));
                data[i].impreso = parseInt(data[i].impreso);
                data[i].fechaliquida = moment(data[i].fechaliquida).isValid() ? moment(data[i].fechaliquida).toDate() : null;
            }
            return data;
        }

        function procDataDocs(data){
            for(var i = 0; i < data.length; i++){
                data[i].idtipodoc = parseInt(data[i].idtipodoc);
                data[i].fechadoc = moment(data[i].fechadoc).toDate();
                data[i].documento = parseInt(data[i].documento);
                data[i].monto = parseFloat(data[i].monto);
                data[i].iddocto = parseInt(data[i].iddocto);
                //data[i].fechaliquida = moment(data[i].fechaliquida).isValid() ? moment(data[i].fechaliquida).toDate() : null;
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

        $scope.getLiquidacion = function(idtran){
            detContSrvc.lstDetalleCont($scope.origenLiq, idtran).then(function(liq){
                $scope.liquidacion = procDataDet(liq);
                //console.log($scope.liquidacion);
                goTop();
            });
        };

        $scope.getDetCont = function(idtran){
            detContSrvc.lstDetalleCont($scope.origen, idtran).then(function(detc){
                $scope.losDetCont = procDataDet(detc);
                $scope.getLiquidacion(idtran);
                goTop();
            });
        };

        function getByIdOrigen(input, id, origen) {
            for(var i = 0; i < input.length; i++) { if (+input[i].id == +id && +input[i].dedonde == +origen) { return input[i]; } }
            return null;
        }

        $scope.getDataTran = function(idtran){
            $scope.editando = true;
            $scope.liquidacion = [];
            tranBancSrvc.getTransaccion(parseInt(idtran)).then(function(d){
                $scope.laTran = processData(d)[0];
                //console.log($scope.laTran);
                $scope.laTran.objBanco = $filter('getById')($scope.losBancos, $scope.laTran.idbanco);
                $scope.strTran  = ($scope.laTran.anticipo === 0 ? '' : 'Anticipo, ') + $scope.laTran.objBanco.nombre + ' (' + $scope.laTran.objBanco.nocuenta + ')' + ', ' ;
                $scope.strTran += $scope.laTran.tipotrans + '-' + $scope.laTran.numero + ', ';
                $scope.strTran += moment($scope.laTran.fecha).format('DD/MM/YYYY') + ', ' + $scope.laTran.moneda + ' ' + $filter('number')($scope.laTran.monto, 2) + ', ' + $scope.laTran.beneficiario;

                if($scope.laTran.anticipo === 1){
                    $scope.laTran.objBeneficiario = [getByIdOrigen($scope.beneficiarios, $scope.laTran.idbeneficiario, $scope.laTran.origenbene)];
                }

                tipoMovTranBanSrvc.getByAbreviatura(d[0].tipotrans).then(function(res){
                    $scope.laTran.objTipotrans = res[0];
                    tipoDocSopTBSrvc.lstTiposDocTB(parseInt(res[0].id)).then(function(d){ $scope.losTiposDocTB = d; });
                });

                tranBancSrvc.lstDocsSoporte(parseInt(idtran)).then(function(det){
                    $scope.losDocsSoporte = procDataDocs(det);
                    $scope.compraspendientes = [];
                    $scope.elDocSop = {fechadoc: moment().toDate(), fechaliquida: null};
                });

                cuentacSrvc.getByTipo($scope.laEmpresa.id, 0).then(function(ctas){
                    $scope.lasCuentasMov = ctas;
                });

                $scope.getDetCont(parseInt(idtran));
                $scope.getLiquidacion(+idtran);

            });
        };

        $scope.updTran = function(data, id){
            data.idbanco = data.objBanco.id;
            data.fechastr = moment(data.fecha).format('YYYY-MM-DD');
            data.tipotrans = data.objTipotrans.abreviatura;
            data.anticipo = data.anticipo != null && data.anticipo != undefined ? data.anticipo : 0;
            data.esajustedc = data.esajustedc != null && data.esajustedc != undefined ? data.esajustedc : 0;
            data.idbeneficiario = (parseInt(data.anticipo) === 0) ? 0 : (data.objBeneficiario[0] != null && data.objBeneficiario[0] != undefined ? data.objBeneficiario[0].id : 0);
            data.origenbene = (parseInt(data.anticipo) === 0) ? 0 : (data.objBeneficiario[0] != null && data.objBeneficiario[0] != undefined ? data.objBeneficiario[0].dedonde : 0);
            tranBancSrvc.editRow(data, 'u').then(function(){
                $scope.laTran = {
                    objBanco: data.objBanco,
                    objTipotrans: null,
                    concepto: ''
                };
                $scope.strTran = '';
                $scope.getLstTran();
                $scope.editando = false;
            });
        };

        $scope.delTran = function(id){
            tranBancSrvc.editRow({id:id}, 'd').then(function(){
                $scope.getLstTran();
            });
        };

        $scope.anular = function(obj){
            var modalInstance = $uibModal.open({
                animation: true,
                templateUrl: 'modalAnulacion.html',
                controller: 'ModalAnulacionCtrl',
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
                tranBancSrvc.editRow(obj, 'anula').then(function(){ $scope.getDataTran($scope.laTran.id); });
            }, function(){ return 0; });
        };

        $scope.addDocSop = function(obj){
            obj.idtranban = parseInt($scope.laTran.id);
            obj.fechadocstr = moment(obj.fechadoc).format('YYYY-MM-DD');
            obj.idtipodoc = obj.objTipoDocTB.id;
            obj.serie = obj.serie != null && obj.serie != undefined ? obj.serie : '';
            obj.iddocto = obj.objDocsPendientes[0] != null && obj.objDocsPendientes[0] != undefined ? obj.objDocsPendientes[0].id : 0;
            obj.montotran = $scope.laTran.monto;
            obj.idempresa = $scope.laEmpresa.id;
            obj.fechaliquidastr = moment(obj.fechaliquida).isValid() ? moment(obj.fechaliquida).format('YYYY-MM-DD') : '';

            tranBancSrvc.getSumDocsSop(parseInt($scope.laTran.id)).then(function(suma){
                suma.totmonto = suma.totmonto != null && suma.totmonto != undefined ? suma.totmonto : 0.0;
                var totMonto = parseFloat(suma.totmonto) + parseFloat(obj.monto);
                //if(totMonto <= parseFloat($scope.laTran.monto)){
                    tranBancSrvc.editRow(obj, 'cd').then(function(){
                        tranBancSrvc.lstDocsSoporte(parseInt($scope.laTran.id)).then(function(det){
                            $scope.losDocsSoporte = procDataDocs(det);
                        });
                        $scope.elDocSop = {fechadoc: moment().toDate(), fechaliquida: null};
                        $scope.getDetCont(+$scope.laTran.id);
                        $scope.getLiquidacion(+$scope.laTran.id);
                    });
                //}else{
                    //toaster.pop({ type: 'error', title: 'Error en el monto.',
                        //body: 'La suma de los montos de los documentos de soporte no puede ser mayor al monto de la transacción.', timeout: 7000 });
                    //$scope.elDocSop.monto = null;
                //};
            });
        };

        $scope.zeroDebe = function(valor){ $scope.elDetCont.debe = parseFloat(valor) > 0 ? 0.0 : $scope.elDetCont.debe; };
        $scope.zeroHaber = function(valor){ $scope.elDetCont.haber = parseFloat(valor) > 0 ? 0.0 : $scope.elDetCont.haber; };

        $scope.addDetCont = function(obj){
            obj.origen = $scope.origen;
            obj.idorigen = parseInt($scope.laTran.id);
            obj.debe = parseFloat(obj.debe);
            obj.haber = parseFloat(obj.haber);
            obj.idcuenta = parseInt(obj.objCuenta[0].id);
            detContSrvc.editRow(obj, 'c').then(function(){
                detContSrvc.lstDetalleCont($scope.origen, parseInt($scope.laTran.id)).then(function(detc){
                    $scope.losDetCont = procDataDet(detc);
                    $scope.elDetCont = {debe: 0.0, haber: 0.0};
                    $scope.searchcta = "";
                });
            });
        };

        $scope.delDetCont = function(obj){
            $confirm({text: '¿Seguro(a) de eliminar esta cuenta?', title: 'Eliminar cuenta contable', ok: 'Sí', cancel: 'No'}).then(function() {
                detContSrvc.editRow({id:obj.id}, 'd').then(function(){ $scope.getDetCont(obj.idorigen); });
            });
        };

        $scope.printVersion = function(){ PrintElem('#toPrint', 'Transacción bancaria'); };

    }]);

    //------------------------------------------------------------------------------------------------------------------------------------------------//
    tranbancctrl.controller('ModalAnulacionCtrl', ['$scope', '$uibModalInstance', 'lstrazonanula', function($scope, $uibModalInstance, lstrazonanula){
        $scope.razones = lstrazonanula;
        $scope.razon = [];
        $scope.anuladata = {idrazonanulacion:0, fechaanula: moment().toDate()};

        $scope.ok = function () {
            $scope.anuladata.idrazonanulacion = $scope.razon.id;
            $scope.anuladata.fechaanulastr = moment($scope.anuladata.fechaanula).format('YYYY-MM-DD');
            //console.log($scope.anuladata);
            $uibModalInstance.close($scope.anuladata);
        };

        $scope.cancel = function () {
            $uibModalInstance.dismiss('cancel');
        };
    }]);

}());
