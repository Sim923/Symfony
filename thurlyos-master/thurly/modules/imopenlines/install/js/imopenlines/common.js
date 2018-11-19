;(function()
{
	BX.namespace("BX.ImOpenLines");
	
	if(typeof(BX.ImOpenLines.UserSelector) == "undefined")
	{
		BX.ImOpenLines.UserSelector = function()
		{
			this._id = "";
			this._settings = {};
			this._fieldId = "";
			this._control = null;

			this._currentUser = null;
			this._componentName = null;
			this._componentObj = null;
			this._componentContainer = null;
			this._serviceContainer = null;

			this._zIndex = 1100;
			this._isDialogDisplayed = false;
			this._dialog = null;

			this._inputKeyPressHandler = BX.delegate(this.onInputKeyPress, this);
			//this._externalClickHandler = BX.delegate(this.onExternalClick, this);
		};

		BX.ImOpenLines.UserSelector.prototype =
		{
			initialize: function(id, settings)
			{
				this._id = id;
				this._settings = settings ? settings : {};
				this._fieldId = this.getSetting("fieldId", "");
				this._componentName = this.getSetting("componentName", "");
				this._componentContainer = BX(this._componentName + "_selector_content");

				this._serviceContainer = this.getSetting("serviceContainer", null);
				if(!BX.type.isDomNode(this._serviceContainer))
				{
					this._serviceContainer = document.body;
				}

				BX.addCustomEvent(window, "BX.Main.Filter:customEntityFocus", BX.delegate(this.onCustomEntitySelectorOpen, this));
				BX.addCustomEvent(window, "BX.Main.Filter:customEntityBlur", BX.delegate(this.onCustomEntitySelectorClose, this));
			},
			getId: function()
			{
				return this._id;
			},
			getSetting: function (name, defaultval)
			{
				return this._settings.hasOwnProperty(name)  ? this._settings[name] : defaultval;
			},
			getSearchInput: function()
			{
				return this._control ? this._control.getLabelNode() : null;
			},
			isOpened: function()
			{
				return this._isDialogDisplayed;
			},
			open: function()
			{
				if(this._componentObj === null)
				{
					var objName = "O_" + this._componentName;
					if(!window[objName])
					{
						throw "BX.ImOpenLines.UserSelector: Could not find '"+ objName +"' user selector.";
					}
					this._componentObj = window[objName];
				}

				var searchInput = this.getSearchInput();
				if(this._componentObj.searchInput)
				{
					BX.unbind(this._componentObj.searchInput, "keyup", BX.proxy(this._componentObj.search, this._componentObj));
				}
				this._componentObj.searchInput = searchInput;
				BX.bind(this._componentObj.searchInput, "keyup", BX.proxy(this._componentObj.search, this._componentObj));
				this._componentObj.onSelect = BX.delegate(this.onSelect, this);
				BX.bind(searchInput, "keyup", this._inputKeyPressHandler);
				//BX.bind(document, "click", this._externalClickHandler);

				if(this._currentUser)
				{
					this._componentObj.setSelected([ this._currentUser ]);
				}
				else
				{
					var selected = this._componentObj.getSelected();
					if(selected)
					{
						for(var key in selected)
						{
							if(selected.hasOwnProperty(key))
							{
								this._componentObj.unselect(key);
							}
						}
					}
					//this._componentObj.displayTab("last");
				}

				if(this._dialog === null)
				{
					this._componentContainer.style.display = "";
					this._dialog = new BX.PopupWindow(
						this._id,
						this.getSearchInput(),
						{
							autoHide: false,
							draggable: false,
							closeByEsc: true,
							offsetLeft: 0,
							offsetTop: 0,
							zIndex: this._zIndex,
							bindOptions: { forceBindPosition: true },
							content : this._componentContainer,
							events:
							{
								onPopupShow: BX.delegate(this.onDialogShow, this),
								onPopupClose: BX.delegate(this.onDialogClose, this),
								onPopupDestroy: BX.delegate(this.onDialogDestroy, this)
							}
						}
					);
				}

				this._dialog.show();
				this._componentObj._onFocus();

				if(this._control)
				{
					this._control.setPopupContainer(this._componentContainer);
				}
			},
			close: function()
			{
				var searchInput = this.getSearchInput();
				if(searchInput)
				{
					BX.unbind(searchInput, "keyup", this._inputKeyPressHandler);
				}

				if(this._dialog)
				{
					this._dialog.close();
				}

				if(this._control)
				{
					this._control.setPopupContainer(null);
				}

			},
			closeSiblings: function()
			{
				var siblings = BX.ImOpenLines.UserSelector.items;
				for(var k in siblings)
				{
					if(siblings.hasOwnProperty(k) && siblings[k] !== this)
					{
						siblings[k].close();
					}
				}
			},
			onCustomEntitySelectorOpen: function(control)
			{
				var fieldId = control.getId();
				if(this._fieldId !== fieldId)
				{
					this._control = null;
					this.close();
				}
				else
				{
					this._control = control;
					if(this._control)
					{
						var current = this._control.getCurrentValues();
						this._currentUser = { "id": current["value"] };
					}
					this.closeSiblings();
					this.open();
				}
			},
			onCustomEntitySelectorClose: function(control)
			{
				if(this._fieldId === control.getId())
				{
					this._control = null;
					this.close();
				}
			},
			onDialogShow: function()
			{
				this._isDialogDisplayed = true;
			},
			onDialogClose: function()
			{
				this._componentContainer.parentNode.removeChild(this._componentContainer);
				this._serviceContainer.appendChild(this._componentContainer);
				this._componentContainer.style.display = "none";

				this._dialog.destroy();
				this._isDialogDisplayed = false;
			},
			onDialogDestroy: function()
			{
				this._dialog = null;
			},
			onInputKeyPress: function(e)
			{
				if(!this._dialog || !this._isDialogDisplayed)
				{
					this.open();
				}

				if(this._componentObj)
				{
					this._componentObj.search();
				}
			},
			/*
			 onExternalClick: function(e)
			 {
			 if(!e)
			 {
			 e = window.event;
			 }

			 if(!this._isDialogDisplayed)
			 {
			 return;
			 }

			 if(BX.getEventTarget(e) !== this.getSearchInput())
			 {
			 this.close();
			 }
			 },
			 */
			onSelect: function(user)
			{
				this._currentUser = user;
				if(this._control)
				{
					//CRUTCH: Intranet User Selector already setup input value.
					var node = this._control.getLabelNode();
					node.value = "";
					this._control.setData(user["name"], user["id"]);
				}
				this.close();
			}
		};
		BX.ImOpenLines.UserSelector.closeAll = function()
		{
			for(var k in this.items)
			{
				if(this.items.hasOwnProperty(k))
				{
					this.items[k].close();
				}
			}
		};

		BX.ImOpenLines.UserSelector.items = {};
		BX.ImOpenLines.UserSelector.create = function(id, settings)
		{
			var self = new BX.ImOpenLines.UserSelector(id, settings);
			self.initialize(id, settings);
			this.items[self.getId()] = self;
			return self;
		}
	}

})();
