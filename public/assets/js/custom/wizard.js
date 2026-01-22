/**
 * Wizard Form Handler
 * Reusable wizard functionality for multi-step forms
 */
(function($) {
    'use strict';

    $.fn.initWizard = function(options) {
        var defaults = {
            validateOnNext: true,
            scrollOnStepChange: true,
            onStepChange: null,
            onInit: null
        };
        
        var settings = $.extend({}, defaults, options);
        var $wizard = this;
        var currentStep = 0;
        var totalSteps = $wizard.find('.wizard-step').length;

        // Initialize wizard
        function initWizard() {
            updateStepDisplay();
            updateNavigationButtons();
            
            if (settings.onInit) {
                settings.onInit(currentStep, totalSteps);
            }
        }

        // Update step display
        function updateStepDisplay() {
            $wizard.find('.wizard-step').removeClass('active');
            $wizard.find('.wizard-step[data-step="' + currentStep + '"]').addClass('active');
            
            $wizard.find('.step-item').removeClass('active');
            $wizard.find('.step-item[data-step="' + currentStep + '"]').addClass('active');
        }

        // Update navigation buttons
        function updateNavigationButtons() {
            var $prevBtn = $wizard.find('#prev-btn');
            var $nextBtn = $wizard.find('#next-btn');
            var $submitBtn = $wizard.find('#submit-btn');

            if (currentStep === 0) {
                $prevBtn.hide();
            } else {
                $prevBtn.show();
            }

            if (currentStep === totalSteps - 1) {
                $nextBtn.hide();
                $submitBtn.show();
            } else {
                $nextBtn.show();
                $submitBtn.hide();
            }
        }

        // Validate current step
        function validateStep(stepIndex) {
            var $step = $wizard.find('.wizard-step[data-step="' + stepIndex + '"]');
            var isValid = true;
            
            $step.find('input[required], textarea[required], select[required]').each(function() {
                var $field = $(this);
                var fieldValue = $field.val();
                var isFileField = $field.attr('type') === 'file';
                var hasFile = isFileField && ($field[0].files.length > 0 || $field.data('has-file'));
                
                if (!fieldValue && !hasFile) {
                    isValid = false;
                    $field.addClass('is-invalid');
                } else {
                    $field.removeClass('is-invalid');
                }
            });

            if (!isValid) {
                if (typeof $.toast !== 'undefined') {
                    var message = typeof window.wizardValidationMessage !== 'undefined' 
                        ? window.wizardValidationMessage 
                        : 'Please fill all required fields';
                    $.toast({
                        text: message,
                        showHideTransition: 'slide',
                        icon: 'error',
                        loaderBg: '#f2a654',
                        position: 'top-right'
                    });
                }
            }

            return isValid;
        }

        // Go to next step
        $wizard.find('#next-btn').on('click', function() {
            if (!settings.validateOnNext || validateStep(currentStep)) {
                if (currentStep < totalSteps - 1) {
                    currentStep++;
                    updateStepDisplay();
                    updateNavigationButtons();
                    
                    if (settings.scrollOnStepChange) {
                        $wizard.find('.wizard-content').scrollTop(0);
                    }
                    
                    if (settings.onStepChange) {
                        settings.onStepChange(currentStep, 'next');
                    }
                }
            }
        });

        // Go to previous step
        $wizard.find('#prev-btn').on('click', function() {
            if (currentStep > 0) {
                currentStep--;
                updateStepDisplay();
                updateNavigationButtons();
                
                if (settings.scrollOnStepChange) {
                    $wizard.find('.wizard-content').scrollTop(0);
                }
                
                if (settings.onStepChange) {
                    settings.onStepChange(currentStep, 'prev');
                }
            }
        });

        // Sidebar step navigation
        $wizard.find('.step-item').on('click', function(e) {
            e.preventDefault();
            var stepIndex = parseInt($(this).data('step'));
            if (stepIndex !== currentStep && stepIndex >= 0 && stepIndex < totalSteps) {
                currentStep = stepIndex;
                updateStepDisplay();
                updateNavigationButtons();
                
                if (settings.scrollOnStepChange) {
                    $wizard.find('.wizard-content').scrollTop(0);
                }
                
                if (settings.onStepChange) {
                    settings.onStepChange(currentStep, 'direct');
                }
            }
        });

        // Initialize
        initWizard();

        // Return public API
        return {
            goToStep: function(stepIndex) {
                if (stepIndex >= 0 && stepIndex < totalSteps) {
                    currentStep = stepIndex;
                    updateStepDisplay();
                    updateNavigationButtons();
                }
            },
            getCurrentStep: function() {
                return currentStep;
            },
            getTotalSteps: function() {
                return totalSteps;
            }
        };
    };

    // Initialize file upload handlers (global)
    $(document).on('change', '.file-upload-default', function() {
        var fileName = $(this).val().split('\\').pop();
        var $infoInput = $(this).closest('.input-group').find('.file-upload-info');
        if ($infoInput.length) {
            $infoInput.val(fileName);
        }
        $(this).data('has-file', true);
    });
    
    $(document).on('click', '.file-upload-browse', function(e) {
        e.preventDefault();
        var $fileInput = $(this).closest('.input-group').find('.file-upload-default');
        if ($fileInput.length) {
            $fileInput.click();
        }
    });
    
    // Mark existing files on page load
    $(document).ready(function() {
        $('.file-upload-default').each(function() {
            var $img = $(this).closest('.input-group, .form-group').find('img');
            if ($img.length && $img.attr('src')) {
                var src = $img.attr('src');
                // Check if it's not a default placeholder image
                if (src && !src.includes('assets/landing_page_images') && !src.includes('no_image_available')) {
                    $(this).data('has-file', true);
                }
            }
        });
    });

})(jQuery);

