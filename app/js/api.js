define([], function(){
	
	var userObj = {"tipo":null}
	
    return{
		
		uploadAjax: function(servClass, servMethod, data, form, handler){
			
			data = this.dataEncode(servClass,servMethod,data);
			data = data.replace("info=","");
			form.append("info",data);
			
			$.ajax({
				url: 'app/server/init.php',
				data: form,
				cache: false,
				contentType: false,
				processData: false,
				method: 'POST',
				type: 'POST', // For jQuery < 1.9
				success : function(response){ 
						
					// console.log(response);
					
					try{
						
						var resp = JSON.parse(atob(response));	
						
						if(resp.data == "000"){
							
							this.target("");

						}else{
							
							handler(resp);

						}

					}catch(e){

						
						console.log(response);
						console.log(e.message);
						
					}
				}
			});
			
		},
    	//Request de ajax
     	ajaxCom: function (servClass, servMethod, data, handler, asyncOpt) {
        		
        		if (typeof(async)==='undefined') async = true;
				if (typeof(file)==='undefined') file = false;
				
				if(!file){
					
					data = this.dataEncode(servClass,servMethod,data);
				}else{
					
					// var info = {};
					// info.className = servClass;
					// info.methodName = servMethod;
					
					// console.log(JSON.stringify(info));

					// data.append("info",btoa(JSON.stringify(info)));
					
				}
		        $.ajax({
			        // la URL para la petición
			        url : 'app/server/init.php',
					processData: false,
					
			        //Se carga api.target
			        target:this.target,
			     
			        // la información a enviar
			        // (también es posible utilizar una cadena de datos)
			        data : data,
			     	
			     	// Determina si se hace sync o async
			     	//por defecto está en true
			     	async:asyncOpt,
			        // especifica si será una petición POST o GET
			        type : 'POST',
			     
			        // el tipo de información que se espera de respuesta
			        dataType : 'html',
			     
			        // código a ejecutar si la petición es satisfactoria;
			        // la respuesta es pasada como argumento a la función
			        success : function(response){ 
						
						// console.log(response);
						
			        	try{
							
							var resp = JSON.parse(atob(response));	
							
							if(resp.data == "000"){
								
								this.target("");

							}else{
								
								handler(resp);

							}

						}catch(e){

							
							console.log(response);
							console.log(e.message);
							
						}
			        	

			        },
			     
			        // código a ejecutar si la petición falla;
			        // son pasados como argumentos a la función
			        // el objeto de la petición en crudo y código de estatus de la petición
			        error : function(XMLHttpRequest, textStatus, errorThrown) {
			            console.log('status:' + XMLHttpRequest.status + ', status text: ' + XMLHttpRequest.statusText);
			        },
			     
			        // código a ejecutar sin importar si la petición falló o no
			        complete : function(xhr, status) {
			            //console.log('Petición realizada');
			        }
			    });

			},
		//Encode data to send 
		dataEncode: function(servClass, servMethod, data){

			var info = {};
			info.className = servClass;
			info.methodName = servMethod;
			info.data = data;

			return "info="+btoa(JSON.stringify(info));

		},
		//email validator
		emailValidator: function(email){

			var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    		return re.test(email);
		},
		//Consume del servidor la interfaz solicitada
		target: function(target){

			
			var data = {};
			data.target = target;

			this.ajaxCom("ui", "load", data, function(response){
				
				
				var resp = response.data;
				var info = resp.info;
				
				$(".content-wrapper").html(info);
				history.pushState("", target, "?"+target);
				
				require([target], function(module){
			  
				  module.initialize();

				});

			},false);
		},
		
		toastMsn : function (msn){
			
			// console.log("entrada");
			$("#toastText").html(msn);
			$('#toastMsn').stop().fadeIn(400).delay(2000).fadeOut(400); //fade out after 2 seconds
			
		},

		getTarget: function(){

			var a = location.href; 
			var b = a.substring(a.indexOf("?")+1);

			if(a == b){

				return "home";
			}else{

				return b;
			}
			

		},
		OptList: function(ID, DESCRIPCION){

			this.id = ID;
        	this.descripcion = DESCRIPCION;


		},
		
		userType: function(){
			
			return userObj.tipo;
			
		},
		chkLogin: function(){

			var self = this;
			this.ajaxCom("user","chkLogin",{},function(response){

				if(response.data.status){
					
					$('#login-box').hide();
					$('#menusWrapper').show();
					
					$('body').removeClass('login').addClass('nav-md');
					
					self.target(self.getTarget());
					userObj.tipo = response.data.info;
					console.log(userObj);
					
				}else{
					
					$('body').removeClass('nav-md').addClass('login');
					$('#menusWrapper').hide(); 
					$('#login-box').show();
					
					self.getTarget("no session");	
					
				}

				
			},false);
		}

    }
});