define(['api','jquery','ko','moment','validate','boostrapDatePicker','bootstrap','ui','moment/locale/es','smartWizard'], function(api,$,ko,moment){
    
    return {
		
		addFormatoModel: function(){
			
			//varaibles
			self.mapArray = {};
			self.separadores = ko.observableArray();
			self.selectedSeparador = ko.observable(null);
			
			self.arrayToMap = ko.observableArray([]);
			self.observableMap = ko.observableArray([]);

			//forma variables
			self.codFormato = ko.observable().extend({ uppercase: true });
			self.formatoDescripcion = ko.observable().extend({ uppercase: true });
			self.fileName = ko.observable();
			
			//optionsvars
			self.bancoList = ko.observableArray();
			self.selectedBanco = ko.observable();

			self.mapList = ko.observableArray();
			
			
			self.loadMaestros = function(){
				
				var opciones = [];
				
				opciones.push(new api.OptList("puntoycoma","Punto y Coma (;)"));
				opciones.push(new api.OptList("coma","Coma (,)"));
				opciones.push(new api.OptList("tab","Tabulador"));
				opciones.push(new api.OptList("space","Espacio"));
				
				self.separadores(opciones);


				api.ajaxCom("bancos","getBancoListSelect",{},function(response){
					
					var tmpOpt = response.data.message;
					var opciones = [];
					
					for(var i = 0; i<tmpOpt.length; i++){
						
						opciones.push(new api.OptList(tmpOpt[i]["ID"],tmpOpt[i]["DESCRIPCION"]));
						
					}
					self.bancoList(opciones);
						
				});

				api.ajaxCom("bancos","getFormatoList",{},function(response){
					
					var tmpOpt = response.data.message;
					var opciones = [];
					
					for(var i = 0; i<tmpOpt.length; i++){
						
						opciones.push(new api.OptList(tmpOpt[i]["ID"],tmpOpt[i]["DESCRIPCION"]));
						
					}
					self.mapList(opciones);
						
				});
				
			} 
			
			self.cahngeMaped = function(index, value){
				
				mapArray[index] = $(value).val(); 	
				

			}
			
			
			/// FUNCIONES GENERALES 

			self.uploadFile = function(){
				
				self.arrayToMap([]);
				
				var fileTmp = document.getElementById('formatoFileUpload').files[0];
				
				if(document.getElementById('formatoFileUpload').files.length > 0){
					
					var info = {};

					var extension  = fileTmp.name.substr( (fileTmp.name.lastIndexOf('.') +1) );
					var extArray = ["xlsx","csv"];
					
					if(extArray.includes(extension)){
						
						if(extension != "csv"){
							
							$("#serparatorId").prop( "disabled", true );
						}
						
						if(extension == "csv" && $("#serparatorId").prop("disabled")){
							
							$("#separatorDiv").css('display', 'block');
							$("#serparatorId").prop( "disabled", false );
														
							// console.log($("#serparatorId").prop("disabled"));
							
							return;
						}
						
						self.fileName(fileTmp.name);
						
						var form = new FormData();
						$.each($('#formatoFileUpload')[0].files, function(i, file) {
							form.append(i, file);
						});
						
						
						if(extension == "csv"){
							
							info.separator = self.selectedSeparador().id;

							if(self.selectedSeparador() == null){

								return;
							}
							
						}
						
						

						api.uploadAjax("bancos","segmentarFormato",info, form, function(response){
							
							if(response.data.status){
								
								self.arrayToMap(response.data.message);
								
							}else{

								alert(response.data.message);
								
							}
							
						});

					}else{

						alert("Extensión no permitida");
						$('#formatoFileUpload').val('');
					}
				}
				
			}
			
			
			///SAMRT WIZARDS ////////
			self.leaveAStepCallback = function(obj,context){
				
				// console.log(context.toStep);
				var step_num= obj.attr('rel');
				return validateSteps(step_num,context);
			}
			
			self.validateSteps = function(step,context){
				
				
				var isStepValid = true;
			  // validate step 1
				if(step == 1){
					
					var isStepValid = false;
					$("#submitPaso1").trigger( "click" ); //paso1Submit
					
					if($("#paso1Form")[0].checkValidity()){
						
						var isStepValid = true;
						$('.buttonFinish').show();
						$(".buttonNext").hide();
					}
					
				}
				
				if(step == 2){
					
					if(context.toStep != 1){
						
						isStepValid = false;
						alert("hola");
					}else{
						
						$('.buttonFinish').hide();
						$(".buttonNext").show();
						
					}
					
					
					
				}	
				
				return isStepValid;
			}
			
			self.onFinishCallback = function(){
				
				// console.log(mapArray);

				var info = {};

				info.banco = self.selectedBanco().id;
				info.codFormato = self.codFormato();
				info.descripcion = self.formatoDescripcion();
				info.map = mapArray;
				info.estructura = self.arrayToMap();

				api.ajaxCom("bancos","guardarFormatoRecaudo",info,function(response){
					
					// console.log(response.data.status);
					if(response.data.status){

						api.aceptMessage("Formato agregado exitosamente","Mensaje",function(){

							location.reload();

						},"Aceptar");

					}else{

						api.aceptMessage("response.data.message","Error",function(){

							location.reload();

						},"Aceptar");
						
					}
						
				});
					
				
			}

			$('#wizard').smartWizard(
				{
					onLeaveStep:leaveAStepCallback,
					onFinish:onFinishCallback,
					enableFinishButton: false,
					enableFinishButton:true,
					labelNext:'Siguiente', // label for Next button
					labelPrevious:'Anterior', // label for Previous button
					labelFinish:'Finalizar'  // label for Finish button        
				}
			);
			///SAMRT WIZARDS END ////////
			
		},
		initialize: function(){
	
			
        	ko.applyBindings(new this.addFormatoModel(), document.getElementById("addFormatoModel"));   
			
			$('.buttonNext').addClass('btn btn-success');
			$('.buttonPrevious').addClass('btn btn-primary');
			$('.buttonFinish').addClass('btn btn-default');
			$('.buttonFinish').hide();
			$('.buttonFinish').hide();
			$(".buttonPrevious").before($(".buttonNext"));
			// $('.stepContainer').height('auto');
			
			$('#formatoFieldUpload').click(function(){
				
				$("#formatoFileUpload").trigger( "click" );
				
				
			})
			
			// self.loadEmpresaTable();
			self.loadMaestros();
			//$('#fechaNac').datetimepicker({format: 'YYYY-MM-DD',locale: 'ru'});
        },
	}
	
	
	
})