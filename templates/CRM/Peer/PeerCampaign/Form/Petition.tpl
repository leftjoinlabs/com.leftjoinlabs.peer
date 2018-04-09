<div id="peer-campaign-form-block" class="crm-accordion-wrapper open">

  <div class="crm-accordion-header">
    {ts}Peer-To-Peer Pages{/ts}
  </div>

  <div class="crm-accordion-body">
    <div class="crm-block crm-form-block crm-form-title-here-form-block">

      <table class="form-layout">
        <tr class="">
          <td class="label">&nbsp;</td>
          <td>{$form.peer_campaign_is_active.html} {$form.peer_campaign_is_active.label}</td>
        </tr>
      </table>

      <div class="spacer"></div>

      <div id="peer-campaign-fields">
        <table class="form-layout">

          <tr class="">
            <td class="label">{$form.peer_campaign_supporter_profile_id.label}</td>
            <td>
              {$form.peer_campaign_supporter_profile_id.html}
              <div class="description">{ts}Supporters will fill out this profile when they create their own pages. The profile must be configured with 'Account creation required'.{/ts}</div>
            </td>
          </tr>

        </table>
      </div>

    </div>
  </div>

</div>

{include
  file="CRM/common/showHideByFieldValue.tpl"
  trigger_field_id    = "peer_campaign_is_active"
  trigger_value       = "true"
  target_element_id   = "peer-campaign-fields"
  target_element_type = "block"
  field_type          = "radio"
  invert              = "false"
}

{* reposition the block *}
<script type="text/javascript">
  cj('#peer-campaign-form-block').insertAfter('.crm-campaign-survey-form-block .form-layout')
</script>
