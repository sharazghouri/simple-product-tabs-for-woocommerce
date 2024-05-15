"use strict";
(function ($) {
  function debounce(fn, delay) {
    var timer = null;
    return function () {
      var context = this,
        args = arguments;
      clearTimeout(timer);
      timer = setTimeout(function () {
        fn.apply(context, args);
      }, delay);
    };
  }

  /**
   * Search terms on typing keywords in Inclusions section
   */
  function termSearch() {
    let self = $(this);
    let taxonomy = self.attr('data-taxonomy');
    let wrapperSelector = self.closest('.swt-inclusion-selector');
    let inclusionType = self.attr('data-type');
    // display the loader
    wrapperSelector.find('.swt-loader').show();
    // hide no results message initially
    wrapperSelector.find('.swt-component-no-results').hide();
    let searchedTermsList = wrapperSelector.find('.solution-box-search-list__list');
    const searchTerm = self.val();
    if (!searchTerm && !searchTerm.length) {
      wrapperSelector.find('.swt-loader').hide();
      return;
    }
    let searchParam = new URLSearchParams({
      search: searchTerm
    });

    // Make WooCommerce REST API call to get terms
    wp.apiFetch({
      path: `/wc/v3/products/${taxonomy}/?${searchParam.toString()}`
    }).then(terms => {
      // hide the loader
      self.closest('.swt-inclusion-selector').find('.swt-loader').hide();
      if (terms.length == 0) {
        // if no terms found, display no results found message
        self.closest('.swt-inclusion-selector').find('.swt-component-no-results').show();
        return;
      }
      let searchedTermsHTML = '';
      terms.map(term => {
        searchedTermsHTML += `<li data-inclusion-id=${term.id} data-inclusion-name="${term.name}" data-inclusion-type="${inclusionType}"><label for="search-list-item-${inclusionType}-0-${term.id}" data-inclusion-type="${inclusionType}" class=" solution-box-search-list__item depth-0"><input type="checkbox" id="search-list-item-${inclusionType}-0-${term.id}" name="search-list-item-${inclusionType}-0" class="solution-box-search-list__item-input" value="">	<span class="solution-box-search-list__item-label"><span class="solution-box-search-list__item-name">${term.name}</span></span></label></li>`;
      });
      searchedTermsList.html(searchedTermsHTML).show();
    });
  }
  $('#swt-category-search, #swt-tag-search').on('keyup', debounce(termSearch, 500));

  /**
   * Display/Hide inclusions sections based on the visibility condition
   */
  $('.sptb_visibility_condition').on('change', function () {
    
    if ($(this).val() === 'yes') {
      $('#inclusions-list.form-table').addClass('hide-section');
    } else {
      $('#inclusions-list.form-table').removeClass('hide-section');
    }
  });
  function selectTerm() {
    const self = $(this);
    const inclusionWrapper = self.closest('.swt-inclusion-selector');
    // the current term that clicked
    const checkedTerm = self.attr('data-inclusion-id');
    const checkedTermName = self.attr('data-inclusion-name');
    const wptInclusionType = self.attr('data-inclusion-type');
    // get list of already added terms
    const selectedTermDOM = inclusionWrapper.find('.solution-box-search-list__selected_terms input[type="hidden"]');
    const selectedTerms = Array.from(selectedTermDOM, term => term.value);
    if (selectedTerms.includes(checkedTerm)) {
      return;
    }
    let termListHTML = `<li><span class="solution-box-selected-list__tag"><span class="solution-box-tag__text" id="solution-box-tag__label-${checkedTerm}"><span class="screen-reader-text">${checkedTermName}</span><span aria-hidden="true">${checkedTermName}</span></span><input type="hidden" name="sptb_${wptInclusionType}_list[]" value="${checkedTerm}"><button type="button" aria-describedby="solution-box-tag__label-${checkedTerm}" class="components-button solution-box-tag__remove" id="solution-box-remove-term" aria-label="${checkedTermName}"><svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="clear-icon" aria-hidden="true" focusable="false"><path d="M12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21ZM15.5303 8.46967C15.8232 8.76256 15.8232 9.23744 15.5303 9.53033L13.0607 12L15.5303 14.4697C15.8232 14.7626 15.8232 15.2374 15.5303 15.5303C15.2374 15.8232 14.7626 15.8232 14.4697 15.5303L12 13.0607L9.53033 15.5303C9.23744 15.8232 8.76256 15.8232 8.46967 15.5303C8.17678 15.2374 8.17678 14.7626 8.46967 14.4697L10.9393 12L8.46967 9.53033C8.17678 9.23744 8.17678 8.76256 8.46967 8.46967C8.76256 8.17678 9.23744 8.17678 9.53033 8.46967L12 10.9393L14.4697 8.46967C14.7626 8.17678 15.2374 8.17678 15.5303 8.46967Z"></path></svg></button></span></li>`;
    inclusionWrapper.find('.solution-box-search-list__selected').removeClass('wpt-hide-selected-terms-section');
    inclusionWrapper.find('.solution-box-search-list__selected').show();
    inclusionWrapper.find('.solution-box-search-list__selected_terms').append(termListHTML);
  }
  $(document).on('click', '.solution-box-search-list__list li', debounce(selectTerm, 50));
  $(document).on('click', '#solution-box-remove-term', function () {
    var self = $(this);
    let parent_list = $(this).parents('ul');
    self.closest('li').remove();
    if (parent_list.find('li').length === 0) {
      $('.solution-box-remove-inclusions').click();
    }
  });
  $('.solution-box-remove-inclusions').on('click', function () {
    const self = $(this);
    const wrapper = self.closest('.swt-inclusion-selector');
    wrapper.find('.solution-box-search-list__selected_terms').empty();
    wrapper.find('.solution-box-search-list__selected').hide();
  });

  /**
   * Change the CPT filter status to a text field
   */
  $('body.post-type-woo_product_tab .wrap .subsubsub').html('<p class="swt-sub-heading">Create additional tabs for your product pages and choose which categories they appear on. For more options,<a target="_blank" href="https://solution-box.com/wordpress-plugins/woocommerce-product-tabs/?utm_source=settings&utm_medium=settings&utm_campaign=settingsinline&utm_content=swtsettings">upgrade to Pro.</a></p>');



   //Accordion - Product Edit page
   const acc = document.getElementsByClassName( 'sptb_accordion' );
   if ( acc ) {
     let i;
     for ( i = 0; i < acc.length; i++ ) {
       const panel = acc[ i ].nextElementSibling;
       if( ! panel.querySelector( '.override-tab-content' ).checked ) {
         panel.querySelector( '.wp-editor-wrap' ).classList.add( 'hidden' );
       }
       acc[ i ].addEventListener( 'click', function() {
         this.classList.toggle( 'active' );
         panel.classList.toggle( 'hidden' );
       } );
     }
   }
 
   // Show the editor field
   const overrideInputs = $( '.sptb_accordion .override-tab-content' );
   if( overrideInputs ) {
     overrideInputs.each( function( i ) {
       let editor = $( this ).parents('.tab-container').find( '.wp-editor-wrap' );
       $( this ).on( 'change', function( e ) {
         editor.toggleClass( 'hidden' );
       })
     } )
   };
})(jQuery);