@extends('layouts.index')

@section('content')
<style>

/* Main content area */
.main-content {
  min-height: 100vh;    /* ensure visible */
  background-repeat: no-repeat;
  background-position: center;
  background-size: cover;
}

/* Custom my-row toolbar */
.my-row {
  position: sticky;
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 0.5rem;
  padding: 1rem 2rem;
  margin: 0em 0em 0 0em;
  
  background: #fafbfc;
  border-radius: 20px;
  box-shadow: 0px 0px 10px 1px rgba(196, 196, 196, 1);
  top: 0;   /* how far from top it sticks */
  z-index: 1000; /* stay above content */
}

.content-container {
  display: flex;
  flex-wrap: wrap;
  gap: 24px;
  padding: 1rem 0rem 2rem 0rem;
  margin: 10px 0em 0em 0em;
  /* min-width:745px; */
  /* background: #fafbfc; */
  /* deeper bottom shadow */
  /* border-radius: 20px; */
  /* box-shadow: 0px 0px 10px 1px rgba(196, 196, 196, 1); */
}

.content-container-side {
  flex: 1;
  padding: 0 0em 1em 0em;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
}

.side-header {
  text-align: center;
  font-size: 1.2em;
  font-weight: 600;

  border-bottom: 2px solid #d2d2d2;
  margin-bottom: 0.8em;
  padding-bottom: 0.3em;
}

/* Basic Reset */
*, *::before, *::after {
  box-sizing: border-box;
}

/* ===== MODAL STYLES scoped to #addPersonModal ===== */

/* Modal Overlay */
#addPersonModal.modal {
  display: none;
  position: fixed;
  z-index: 3000;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  overflow: auto;
  background: rgba(0, 0, 0, 0.4);
  padding: 20px;
}

/* Modal Dialog */
#addPersonModal .modal-dialog {
  background: #fff;
  max-width: 720px;  /* widened */
  width: 90%;        /* responsive */
  margin: 5% auto;
  border-radius: 5px;
  overflow: hidden;
  animation: slideIn 0.3s ease-out;
}

/* Header/Footer */
#addPersonModal .modal-header,
#addPersonModal .modal-footer {
  padding: 1rem;
  background: #fafbfc;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
}

#addPersonModal .modal-title {
  margin: 0;
  font-size: 24px;
  flex: 1;
  text-align: center;
}

#addPersonModal .modal-body {
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

/* Rows & Groups */
#addPersonModal .form-row {
  display: flex;
  gap: 15px;
  flex-wrap: nowrap;
}

#addPersonModal .form-group {
  display: flex;
  flex-direction: column;
  flex: 1;
  min-width: 200px;
}

#addPersonModal .form-group-small {
  flex: 0 0 30%; /* narrower column */
}

/* Labels & Inputs */
#addPersonModal label {
  margin-bottom: 0.4rem;
  font-weight: 500;
}

#addPersonModal input,
#addPersonModal select {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 14px;
}

/* Animation */
@keyframes slideIn {
  from { transform: translateY(-30px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

/* ===== END MODAL SCOPING ===== */

.person-card {
  position: relative;
  min-height: 190px;
  background: #f7faf7;
  border: 1px solid #d1d5db;
  border-radius: 12px;
  height: 100%;
  padding: 1em 1.5em 3em 1.5em;
  box-shadow: 0px 1px 4px 1px rgba(196, 196, 196, 1);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  transition: box-shadow 0.3s ease, transform 0.3s ease;
}
.person-card:hover {
  transform: translateY(-5px);
  box-shadow: 0px 0px 16px 4px rgba(230, 242, 230, 1);
}

.person-card-contents .person-detail {
    display: flex;
    flex-wrap: wrap;
}

.person-card-contents {
  font-weight: 400;
  color: hsl(0, 0%, 40%); /* softer gray-brown for body text */
}

.person-card-contents h4 {
  font-weight: 700;
  color: hsl(42, 44%, 12%); /* strong dark header */
}

.person-card-contents label { 
    font-weight: 400;
    color: hsl(211, 17%, 53%); /* cool gray to recede */
    width: 62px;
    flex-shrink: 0;
}

.person-card-contents span {
    color: hsl(0, 0%, 40%);
    flex: 1;
    word-break: break-word;
}

.person-position {
  font-weight: 700;

  color: hsl(42, 44%, 22%);
  letter-spacing: 0.2px;             /* tiny spacing for clarity */
}

.person-detail:first-of-type {       /* only the Position row */
  margin-top: 2px;                   /* space above */
  margin-bottom: 2px;
  font-size:15px;                /* space below */
}

.select2-container {
  z-index: 6000 !important; /* higher than modal (5000) */
}

.select2-dropdown {
  z-index: 6001 !important;
}

.select2-container .select2-selection--single .select2-selection__arrow {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  right: 4px; /* Adjust as needed for horizontal positioning */
  height: auto; /* Ensure height doesn't interfere with centering */
}
/* limit text length so X + arrow fit */
.select2-selection__rendered {
  display: block;
  max-width: calc(98%); /* leaves space for X + arrow */
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  padding-right: 0; /* avoid double spacing */
}

.select2-selection .select2-selection--single{
  overflow: hidden;
  max-width: 250px;
}

.select2-selection__clear {
    font-size: 1.5em; /* Adjust as needed */
    /* You can also adjust padding, line-height, etc. for better visual alignment */
    padding: 4px 0px 0 0px; 
    line-height: 1; /* Ensure the 'x' is vertically centered */

}
/* Close Button */
.close-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  font-size: 25px;
  font-weight: bold;
  line-height: 1;
  border: none;
  background: transparent;
  padding: 0;
}

/* Base for all green action buttons */
.btn-green {
  background-color: #a1cf6b;
  border: none;
  color: white;
}

.btn-gray {
  background-color: #8aa2a9;
  border: none;
  color: white;
}

.btn-red {
  background-color: rgba(228, 169, 169, 1);
  color: white;
  border: none;
}

.btn-white {
  border: 1px solid #8aa2a9;
  color: gray;
  border-radius: 6px;
}

/* Uniform sizing (Edit + Save + Cancel) */
.btn-uniform {
  padding: 6px 8px;
  border-radius: 8px;
  margin:0;
  font-size: 1em;
  min-width: 92px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

/* Larger button style (for Add Person) */
.btn-large {
  padding: 12px 0.8em;
  font-size: 1.2em;
  width: 150px;
  margin:0;
  line-height: 1.4;
  border-radius: 8px;
  white-space: normal;
  text-align: center;
}

button {
  transition: filter 0.3s ease; /* Smooth transition for the filter */
  cursor: pointer;
}

button:hover {
  filter: brightness(0.90); /* Darkens the button by 5% */
}

.close-btn:hover
{
  filter: brightness(0.5); /* Darkens the button by 50% */
}

input::placeholder {
    font-style: italic;
}

</style>

<!-- background-image: url("{{ asset("public/images/backgrounds/mint_waves.svg") }}"); -->
<div class="main-content" style='flex:1; flex-direction:row; '>
  <!-- <div class="test" style="height:100%; width:100%; background-color: transparent;"></div> -->
  <div class="my-row">
    <h2 class="my-row-text" style ="font-size: 26px;">IAR Signatories Utility</h2>
    <div class="my-row-tools"
         style="display:flex; flex-direction:row; gap:1em; align-items: center; flex-wrap: wrap; ">
      <div class="my-row-search-wrapper" style = " padding: 0px 0px 0px 4px; border:none; border-width:1px; border-radius:8px; display:flex; flex-direction:row; gap:1em; align-items: center; flex-wrap: wrap; ">
        <p style = "display:flex; align-items: center; flex-wrap: wrap; margin: 0 0 0 0"><i class="fa fa-search" style="padding-right: 4px;"></i>Search by: </p>
        
        <select id="search_name_main" class="form-control select_class" style="margin: 0 0 0 0; width:240px;">
          <option></option>
            @foreach ($people as $person)
              <option value="{{ $person['id'] }}">
              {{ $person['complete_name'] }}</option>
            @endforeach
        </select>
          
        <select id="search_province_main" class="form-control select_class" style="margin: 0 0 0 0; width:240px;">
          <option></option>
            @foreach ($people as $person)
                @foreach ($person['provinces'] as $province)
                  <option data-id="{{ $person['id'] }}"
                    data-personname="{{ $person['complete_name'] }}"
                    data-provdesc="{{$province['provDesc']}}"
                    value="{{$province['provCode']}}">
                  {{ $province['provDesc'] }}</option>
                @endforeach
            @endforeach
        </select>
      </div>

      <button type="button"
            class="btn-green btn-large"
            onclick="openPersonModal(this)"
            data-title="Add"
            data-person-id=""
            data-honorific-prefix=""
            data-complete-name=""
            data-post-nominal=""
            data-sex=""
            data-position-id=""
            data-position-name=""
            data-cell-number=""
            data-email=""
            data-is-right="0"
            data-provinces="[]">
        Add Signatory<i class="fa fa-plus" style="padding-left: 4px;"></i>
      </button>
    </div>
    
  </div>
  <!--TOAST -->
  @php
      $toastMessage = session('toast_message');
      $toastColor = session('toast_color', '#4CAF50'); // default green
  @endphp

  @if($toastMessage)
  <div id="toast" style="
      position: fixed;
      top: 20px;
      right: 20px;
      background: {{ $toastColor }};
      color: #fff;
      padding: 15px 20px;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.2);
      z-index: 9999;
      font-size: 14px;
  ">
      {{ $toastMessage }}
  </div>

  <script>
      setTimeout(() => {
          const toast = document.getElementById('toast');
          if (toast) {
              toast.style.transition = "opacity 0.5s ease";
              toast.style.opacity = 0;
              setTimeout(() => toast.remove(), 500);
          }
      }, 3000); // you can also make this configurable if you want
  </script>
  @endif
  <!--end TOAST -->

  <!-- contents -->
    
    
    <div class="content-container">
      
      @php
          $sides = [
              ['label' => 'Left Side', 'value' => 0],
              ['label' => 'Right Side', 'value' => 1],
          ];
      @endphp

      @foreach ($sides as $side)
        <div class="content-container-side" >
          
          <h4  class="side-header">
            {{ $side['label'] }}
          </h4>

          <div class="grid-person-div" style="
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                gap: 1em;
                align-items: stretch;
                grid-auto-rows: 1fr;
          ">
            
            @php $counter = 0; @endphp
            @foreach ($people as $person)
                @if ($person['is_right'] == $side['value'])
                    @php $counter++; @endphp

                    @php
                        $provinceCodes = [];
                        if (!empty($person['provinces'])) {
                            foreach ($person['provinces'] as $p) {
                                $provinceCodes[] = $p['provCode'];
                            }
                        }
                    @endphp

                    <div id="person-card-{{ $person['id'] }}" class="person-card" 
                        style="position: relative; min-height: 190px; background: #f7faf7; border: 1px solid #d1d1d1;">

                          <div class="person-card-contents" style="flex-grow:1; width: 100%; padding: 0em 1em 1em 1em;">
                            {{-- Full name --}}
                            <h4>
                                {{ trim(implode(' ', array_filter([$person['honorific_prefix'], $person['complete_name'], $person['post_nominal']]))) }}
                            </h4>

                            {{-- Other details --}}
                            <div class="person-detail">
                                <label>Position:</label>
                                <span class="person-position">{{ $person['position'] }}</span>
                            </div>

                            <div class="person-detail">
                                <label>Sex:</label>
                                <span>
                                    @if(strtolower($person['sex']) === 'm')
                                        Male
                                    @elseif(strtolower($person['sex']) === 'f')
                                        Female
                                    @else
                                        {{ $person['sex'] }}
                                    @endif
                                </span>
                            </div>

                            <div class="person-detail">
                                <label>Cell #:</label>
                                <span>{{ $person['cell_number'] }}</span>
                            </div>

                            <div class="person-detail">
                                <label>Email:</label>
                                <span>{{ $person['email'] }}</span>
                            </div>
                          </div>

                        {{-- Buttons Wrapper (full-width at bottom, centered) --}}
                        <div class="person-card-buttons-wrapper" style="position: absolute; bottom: 12px; left: 0; right: 0;
                                    display: flex; flex-direction: row; align-items: center; justify-content: space-evenly; gap: 0.0em;">

                            {{-- Edit Button --}}
                            <button type="button"
                                    class="btn-green btn-uniform"
                                    onclick="openPersonModal(this)"
                                    data-title="Edit"
                                    data-person-id="{{ $person['id'] }}"
                                    data-honorific-prefix="{{ isset($person['honorific_prefix']) ? e($person['honorific_prefix']) : '' }}"
                                    data-complete-name="{{ isset($person['complete_name']) ? e($person['complete_name']) : '' }}"
                                    data-post-nominal="{{ isset($person['post_nominal']) ? e($person['post_nominal']) : '' }}"
                                    data-sex="{{ $person['sex'] }}"
                                    data-position-id="{{ isset($person['position_id']) ? $person['position_id'] : '' }}"
                                    data-position-name="{{ isset($person['position']) ? e($person['position']) : '' }}"
                                    data-cell-number="{{ isset($person['cell_number']) ? e($person['cell_number']) : '' }}"
                                    data-email="{{ isset($person['email']) ? e($person['email']) : '' }}"
                                    data-is-right="{{ isset($person['is_right']) ? $person['is_right'] : '0' }}"
                                    data-provinces="{{ htmlspecialchars(json_encode($provinceCodes), ENT_QUOTES, 'UTF-8') }}">
                                Edit<i class="fa fa-edit" style="padding-left: 4px;"></i>
                            </button>

                            {{-- Delete Button --}}
                            <form method="POST" action="{{ route('iar_util.delete_person') }}" style="display:inline; " 
                            onsubmit="return confirm('Are you sure you want to delete this person?');">
                                {{ csrf_field() }}
                                <input type="hidden" name="person_id" value="{{ $person['id'] }}">
                                <button type="submit" class="btn-uniform btn-red">
                                    Delete<i class="fa fa-trash" style="padding-left: 4px;"></i>
                                </button>
                            </form>

                            {{-- Provinces Button --}}

                        <button type="button" 
                                class="btn-uniform btn-gray" 
                                onclick="toggleProvinces(this)">
                          <span class="btn-label">Provinces</span>
                          <i class="fa fa-caret-up ml-2" style="padding-left: 8px;"></i>
                        </button>

                        <script>
                            const personProvinces_{{ $person['id'] }} = {!! json_encode($provinceCodes, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
                        </script>

                        {{-- Provinces List (aligned above the button, right side with margin) --}}
                        <div class="provinces-list" 
                            style="display:none; position: absolute; right: 19.5px; bottom: calc(100% + 1px);
                                  background: #fafbfc; border: 1px solid #ccc; padding: 0.25em 1.2em; border-radius: 6px; 
                                  box-shadow: 0 2px 10px rgba(0,0,0,0.15); max-width: 260px; font-size: 1em; 
                                  color: #333; max-height: 162px; overflow-y: auto;">
                            <!-- <strong>Provinces:</strong> -->
                            <ul style="margin:0em 0 0 0.5em; padding:0;">
                                @foreach ($person['provinces'] as $province)
                                    <li>{{ $province['provDesc'] }}</li>
                                @endforeach
                            </ul>
                        </div>
                        
                      </div>
                        
                    </div>
                @endif
            @endforeach

          </div>
        </div>
      @endforeach

    <!-- </div> -->

  <!-- end of contents -->

</div>

<!-- Add/Edit Person Modal -->
<div class="modal" id="addPersonModal">
  <div class="modal-dialog">
    <form id="personForm" method="POST" action="{{ route('IAR_util.save_person') }}">
      {{ csrf_field() }}
      <input type="hidden" name="person_id" id="personId" value="{{ old('person_id', '') }}">

      <!-- Modal Header -->
      <div class="modal-header">
        <h5 class="modal-title">Add New/Edit Person</h5>
        <button type="button" class="close-btn" onclick="closeModal('addPersonModal')">&times;</button>
      </div>

      <!-- Modal Body -->
      <div class="modal-body">

        <!-- Row 1 -->
        <div class="form-row">
          <div class="form-group form-group-small">
            <label>Honorific Prefix</label>
            <input type="text" name="honorific_prefix" class="form-control" value="{{ old('honorific_prefix') }}" 
                  placeholder="Dr., Engr., Atty., Hon., etc." title="e.g. Mr., Mrs., Ms., Dr., Engr., Atty., Hon.">
            @if ($errors->has('honorific_prefix'))
              <small class="text-danger">{{ $errors->first('honorific_prefix') }}</small>
            @endif
          </div>

          <div class="form-group">
            <label>Name <em>(First Middle-Initial. Last Ext.)</em><span style="color: red;"> *</span></label>
            <input type="text" name="complete_name" class="form-control" value="{{ old('complete_name') }}" required 
                  placeholder="Juan A. Dela Cruz Jr." title="Complete name is required in the format: First Middle-Initial. Last Ext.">
            @if ($errors->has('complete_name'))
              <small class="text-danger">{{ $errors->first('complete_name') }}</small>
            @endif
          </div>

          <div class="form-group form-group-small">
            <label>Post Nominal</label>
            <input type="text" name="post_nominal" class="form-control" value="{{ old('post_nominal') }}" placeholder="CPA, PhD, D.P.A., etc."
                  title="e.g. B.S., B.A., M.S., M.A., PhD, M.D., CPA, PE, RN, RPh, R.A., LPT (multiple post-nominals allowed, separated by commas)">
            @if ($errors->has('post_nominal'))
              <small class="text-danger">{{ $errors->first('post_nominal') }}</small>
            @endif
          </div>
        </div>

        <!-- Row 2 -->
        <div class="form-row">
          <div class="form-group">
            <label>Position</label>
            <select name="position_id" id="positionSelect" class="form-control">
              <option value="" disabled selected>Select Position Here</option>
              @foreach ($positions as $position)
                <option value="{{ $position->position_id }}">{{ $position->position_name }}</option>
              @endforeach
            </select>
            @if ($errors->has('position_id'))
              <small class="text-danger">{{ $errors->first('position_id') }}</small>
            @endif
          </div>

          <div class="form-group form-group-small">
            <label>Sex</label>
            <select name="sex" class="form-control">
              <option value="M" {{ old('sex') == 'M' ? 'selected' : '' }}>Male</option>
              <option value="F" {{ old('sex') == 'F' ? 'selected' : '' }}>Female</option>
            </select>
            @if ($errors->has('sex'))
              <small class="text-danger">{{ $errors->first('sex') }}</small>
            @endif
          </div>
        </div>

        <!-- Row 3 -->
        <div class="form-row">
          <div class="form-group">
            <label>New Position (optional)</label>
            <input type="text" id="positionNameInput" name="position_name" class="form-control"
                  placeholder="Type new position if not listed" value="{{ old('position_name') }}">
            @if ($errors->has('position_name'))
              <small class="text-danger">{{ $errors->first('position_name') }}</small>
            @endif
          </div>

          <div class="form-group form-group-small">
            <label>Contact Number</label>
            <input type="text" name="cell_number" pattern="((09)[0-9]{9})"
                  title="Must start with 09 and be 11 digits, e.g., 09123456789"
                  class="form-control" maxlength="11" placeholder="09xxxxxxxxx" value="{{ old('cell_number') }}">
            @if ($errors->has('cell_number'))
              <small class="text-danger">{{ $errors->first('cell_number') }}</small>
            @endif
          </div>
        </div>

        <!-- Row 4 -->
        <div class="form-row">
          <div class="form-group form-group-small">
            <label>Side on Signatory</label>
            <select name="is_right" class="form-control" required>
              <option value="1" {{ old('is_right') == 1 ? 'selected' : '' }}>Right</option>
              <option value="0" {{ old('is_right') == 0 ? 'selected' : '' }}>Left</option>
            </select>
            @if ($errors->has('is_right'))
              <small class="text-danger">{{ $errors->first('is_right') }}</small>
            @endif
          </div>

          <div class="form-group">
            <label>Email</label>
            <input type="email" id="email" name="email" class="form-control"
                  placeholder="name@example.com" maxlength="255" value="{{ old('email') }}"
                  title="Enter a valid email address, e.g., name@example.com">
            @if ($errors->has('email'))
              <small class="text-danger">{{ $errors->first('email') }}</small>
            @endif
          </div>
        </div>

      </div>

      <!-- Modal Footer -->
      <div class="modal-footer">
        <button type="button" class="btn-uniform btn-gray" onclick="openModal('provinceTaggerModal');" style="padding-left: 12px; padding-right: 12px ">
          Assign or Update AOR - Covered RCEF Provinces<i class="fa fa-list" style="padding-left: 4px;"></i>
        </button>

        <button type="submit" class="btn-uniform btn-green">
          Save <i class="fa fa-floppy-o" style="padding-left: 4px;"></i>
        </button>

        <button type="button" class="btn-uniform btn-white" onclick="closeModal('addPersonModal')">
          Cancel
        </button>
      </div>

      <input type="hidden" name="regions_provinces" id="regionsProvincesInput" value="{{ old('regions_provinces') }}">
    </form>
  </div>
</div>

@if ($errors->any())
<script>
  document.addEventListener('DOMContentLoaded', function() {
    openModal('addPersonModal');
  });
</script>
@endif

<!-- Modal END -->

<!-- Province Tagger Modal -->
<div class="modal" id="provinceTaggerModal"
     style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:4000;">
  <div class="modal-dialog"
       style="background:white; margin:auto; padding:0; border-radius:8px; max-width:400px; width:90%; height:90%; display:flex; flex-direction:column;">
    <div class="modal-content" style="height:100%; display:flex; flex-direction:column;">

      <div class="modal-header" style="position: relative; padding: 10px 10px 10px 10px; border-bottom:1px solid #ccc;">
        <button type="button" 
                class="close-btn"
                onclick="closeModal('provinceTaggerModal')">&times;</button>

        <div style="display: flex; flex-direction: column; gap: 6px;">
          <h3 class="modal-title" style="margin:0; font-size:18px;">Select Regions / Provinces</h3>
          <div class ="prov-modal-row2"; style="display: flex; flex-direction: row; justify-content: space-between;">
            <span id="signatorySideLabel" style="font-size: 14px; color: #666; font-style: italic;"></span>
               <div style="display:flex; flex-direction:row; margin-right:5px">
                     <div style="width: 220px; display: flex; justify-content: flex-end; margin: 0;">
                        <select id="search_province" class="form-control select_class" style="width: 220px;">
                            <option></option>
                            @foreach ($regionsWithProvinces as $region)
                                @foreach ($region['provinces'] as $province)
                                    <option data-region="{{ $region['region_code'] }}"
                                            data-provdesc="{{ $province['provDesc'] }}"
                                            value="{{ $province['provCode'] }}">
                                        {{ $province['provDesc'] }}
                                    </option>
                                @endforeach
                            @endforeach
                        </select>
                     </div>
                  <!-- <input id = "prov-modal-search" type="search" placeholder="Search.." style="padding: 0px 5px; border:1px solid #ccc; min-width:240px; border-radius:4px; height:30px; text-transform: uppercase;" > -->
                  <!-- <button type="button" class="btn btn-default" style="margin:0 0 0 0; padding: 5px 5px 0 5px ">
                    <span class="glyphicon glyphicon-search" ></span>
                  </button> -->
               </div>
          </div>
        </div>
      </div>

      <div class="modal-body" style="flex:1; overflow-y:auto; padding:10px;">

        {{-- Build assigned lists (left/right) from $people --}}
        @php
            $assignedLeft = [];
            $assignedRight = [];

            if (!empty($people) && is_array($people)) {
                foreach ($people as $other) {
                    if (empty($other['provinces']) || !is_array($other['provinces'])) continue;
                    foreach ($other['provinces'] as $pv) {
                        if (!isset($pv['provCode'])) continue;
                        if (isset($other['is_right']) && (int)$other['is_right'] === 1) {
                            $assignedRight[] = $pv['provCode'];
                        } else {
                            $assignedLeft[] = $pv['provCode'];
                        }
                    }
                }
                $assignedLeft = array_values(array_unique($assignedLeft));
                $assignedRight = array_values(array_unique($assignedRight));
            }
        @endphp

        {{-- Render all regions & provinces (static template) --}}
        @foreach ($regionsWithProvinces as $region)
            <div id = "{{ $region['region_code'] }}" class="region-block" style="margin-bottom:8px; padding:8px; border:1px solid #ccc; border-radius:4px;">
                <label style="font-weight:bold; display:block; margin-bottom:4px;">
                    <input type="checkbox" class="region-checkbox"
                           data-region="{{ $region['region_code'] }}"
                           value="region:{{ $region['region_code'] }}">
                    {{ $region['region_name'] }} (Entire Region) <!--{{ $region['region_code'] }}-->
                </label>

                <div class="province-list" style="padding-left:15px ;">
                    @foreach ($region['provinces'] as $province)
                        @php
                            $provCode = $province['provCode'] ? $province['provCode'] : '';
                            $assignedLeftFlag = in_array($provCode, $assignedLeft) ? 1 : 0;
                            $assignedRightFlag = in_array($provCode, $assignedRight) ? 1 : 0;
                        @endphp

                        <div style="margin-bottom:2px;">
                            <label style="display:flex; align-items:center; gap:4px;">
                                <input type="checkbox"
                                       class="province-checkbox"
                                       data-region="{{ $region['region_code'] }}"
                                       data-assigned-left="{{ $assignedLeftFlag }}"
                                       data-assigned-right="{{ $assignedRightFlag }}"
                                       value="province:{{ $provCode }}">
                                {{ $province['provDesc'] }}
                            </label>
                        </div>
                    @endforeach
                </div>

            </div>
        @endforeach

      </div>

      <div class="modal-footer" style="padding:5px; border-top:1px solid #ccc; display:flex; justify-content:flex-end; gap:5px;">
        <!-- Add footer buttons if needed -->
      </div>

    </div>
  </div>
</div>
<!-- END Province Tagger Modal -->
@endsection

@stack('scripts')

<!-- 0. Global state -->
@push('scripts')
<script>
window.currentPersonId = null;
window.ownerProvinces = [];
window.selectedProvincesPerSide = { '0': [], '1': [] };
window.provinceOwnership = {};
let modalStack = [];

const regionList = [];
@foreach ($regionsWithProvinces as $region)
   regionList.push("{{$region['region_code']}}")     
@endforeach
window.regionListString = regionList.map(String);

@if(isset($people) && count($people))
    @foreach($people as $person)
        @foreach($person['provinces'] as $prov)
            window.provinceOwnership["{{ $prov['provCode'] }}"] = window.provinceOwnership["{{ $prov['provCode'] }}"] || [];
            window.provinceOwnership["{{ $prov['provCode'] }}"].push({
                person_id: {{ $person['id'] }},
                isRight: "{{ $person['is_right'] }}"
            });
        @endforeach
    @endforeach
@endif

const peopleJS = {!! json_encode($people) !!};
const peopleListID = [];
for (const person of peopleJS) {
    peopleListID.push(person.id);
}

</script>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('personForm');
    const emailInput = form.querySelector('input[name="email"]');

    form.addEventListener('submit', function (e) {
        const email = emailInput.value.trim();

        if (email === "") {
            return;
        }

        // Regex that forbids double .com at the end
        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[A-Za-z]{2,}$/;
        const doubleComRegex = /\.com\.com$/i;

        if (!emailRegex.test(email)) {
            e.preventDefault();
            alert("Invalid email format.");
            return;
        }

        if (doubleComRegex.test(email)) {
            e.preventDefault();
            alert("Email cannot end with .com.com");
            return;
        }

    });
});
</script>
@endpush

<!-- 1. Position Input / Select Mutual Exclusivity -->
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const positionSelect = document.getElementById('positionSelect');
    const positionNameInput = document.getElementById('positionNameInput');

    if (positionNameInput) {
        positionNameInput.addEventListener('input', () => {
            if (positionNameInput.value.trim()) positionSelect.value = "";
        });
    }
    if (positionSelect) {
        positionSelect.addEventListener('change', () => {
            if (positionSelect.value !== "") positionNameInput.value = "";
        });
    }
});
</script>
@endpush

<!-- 2. Modal Open/Close -->
@push('scripts')
<script>
function openModal(id){
    const el = document.getElementById(id);
    if(!el) return;
    modalStack.push(id);

    el.style.display = 'block';

    if(typeof initRegionCheckboxes === 'function'){
        initRegionCheckboxes();
    }

}

function closeModal(id){
    const el = document.getElementById(id);
    if(!el) return;
    modalStack.pop();

    window.selectedProvincesPerSide = { '0': [], '1': [] };

    el.style.display = 'none';
}
</script>
@endpush

@push('scripts')
<script>
  // -------------------------------
  // Toggle Provinces per Person Card
  // -------------------------------
  function toggleProvinces(button) {
    const card = button.closest('.person-card');
    const provincesList = card.querySelector('.provinces-list');
    if (!provincesList) return;

    const label = button.querySelector('.btn-label');
    const icon = button.querySelector('i');

    if (provincesList.style.display === 'none' || provincesList.style.display === '') {
      provincesList.style.display = 'block';
      label.textContent = 'Provinces';
      icon.classList.remove('fa-caret-up');
      icon.classList.add('fa-caret-down');
    } else {
      provincesList.style.display = 'none';
      label.textContent = 'Provinces';
      icon.classList.remove('fa-caret-down');
      icon.classList.add('fa-caret-up');
    }
  }

  // -------------------------------
  // Init Select2
  // -------------------------------
  $('#search_name_main').select2({
      width: '220px',
      allowClear: true,
      placeholder: "Name"
  });

  $('#search_province_main').select2({
      width: '220px',
      allowClear: true,
      placeholder: "Province",
  });

  // -------------------------------
  // Prevent auto re-open after programmatic clear/unselect
  // -------------------------------
  $('#search_name_main, #search_province_main').on('select2:unselecting', function(ev) {
    if (ev.params && ev.params.args && ev.params.args.originalEvent) {
      ev.params.args.originalEvent.stopPropagation();
    } else {
      $(this).one('select2:opening', function(ev) { ev.preventDefault(); });
    }
  });

  // -------------------------------
  // Mutual clearing on opening
  // -------------------------------
  $('#search_name_main').on('select2:opening', function () {
      if ($('#search_province_main').val()) {
          $('#search_province_main').val(null).trigger('change').trigger('select2:close');
      }
  });

  $('#search_province_main').on('select2:opening', function () {
      if ($('#search_name_main').val()) {
          $('#search_name_main').val(null).trigger('change').trigger('select2:close');
      }
  });

  // -------------------------------
  // Name Filter
  // -------------------------------
  $('#search_name_main').on('change', function () {
      const prefix = 'person-card-';
      let selectedID = $(this).val();

      // Always hide province lists
      document.querySelectorAll('.provinces-list').forEach(el => {
          el.style.display = 'none';
      });

      if (!selectedID) {
          for (let i = 0; i < peopleListID.length; i++) {
              document.getElementById(prefix + peopleListID[i]).style.display = 'block';
          }
          return;
      }

      for (let i = 0; i < peopleListID.length; i++) {
          document.getElementById(prefix + peopleListID[i]).style.display =
              (peopleListID[i] == selectedID) ? 'block' : 'none';
      }
  });

  // -------------------------------
  // Province Filter
  // -------------------------------
  $('#search_province_main').on('change', function () {
      const prefix = 'person-card-';
      let selectedProvince = $(this).val();

      // Always hide province lists
      document.querySelectorAll('.provinces-list').forEach(el => {
          el.style.display = 'none';
      });

      if (!selectedProvince) {
          for (let i = 0; i < peopleListID.length; i++) {
              document.getElementById(prefix + peopleListID[i]).style.display = 'block';
          }
          return;
      }

      const ids = getIdByProvCode(selectedProvince);

      for (let i = 0; i < peopleListID.length; i++) {
          document.getElementById(prefix + peopleListID[i]).style.display = 'none';
      }
      for (let i = 0; i < ids.length; i++) {
          const el = document.getElementById(prefix + ids[i]);
          if (el) el.style.display = 'block';
      }
  });

  // -------------------------------
  // Helper: find people by province code
  // -------------------------------
  function getIdByProvCode(targetProvCode) {
    if (!targetProvCode) return [];
    const people_id = [];
    for (const person of peopleJS) {
      if (!person.provinces) continue;
      for (const province of person.provinces) {
        if (province && (province.provCode == targetProvCode)) {
          people_id.push(person.id);
          break;
        }
      }
    }
    return people_id;
  }
</script>
@endpush


<!-- 4. Select2 Province tagger Initialization and some logic -->
@push('scripts')
<script>

  // Initialize Select2
  $('#search_province').select2({
    width: '220px',
    allowClear: true,
    placeholder: "Search for Province"
  });

  $('#search_province').on('select2:unselecting', function(ev) {
      if (ev.params.args.originalEvent) {
          // When unselecting (in multiple mode)
          ev.params.args.originalEvent.stopPropagation();
      } else {
          // When clearing (in single mode)
          $(this).one('select2:opening', function(ev) { ev.preventDefault(); });
      }
  });

  $('#search_province').on('change', function () {
    let selectedOption = $(this).find('option:selected');

        let regionListToSplice = [...window.regionListString];
        let hideDiv;
        if (selectedOption.length === 0 || selectedOption.val() === '') {
            for (let i = 0; i < regionListToSplice.length; i++) {
                hideDiv = document.getElementById(regionListToSplice[i]);
                hideDiv.style.display = 'block'; 
            }
        }
        else{
            let selectedRegionCode = selectedOption.data('region');

            let index = regionListToSplice.indexOf(selectedRegionCode);
            if (index !== -1 )
            regionListToSplice.splice(index, 1);

            for (let i = 0; i < regionListToSplice.length; i++) {
                hideDiv = document.getElementById(regionListToSplice[i]);
                hideDiv.style.display = 'none'; 
            }
            let showDiv = document.getElementById(selectedRegionCode);
            showDiv.style.display = 'block';
        }
  });
</script>
@endpush

<!-- 5. Province sync functions -->
@push('scripts')
<script>
function syncRegionCheckboxes(){
    document.querySelectorAll('.region-checkbox').forEach(regionCb=>{
        const regionCode = regionCb.dataset.region;
        const provinces = Array.from(document.querySelectorAll(`.province-checkbox[data-region="${regionCode}"]`));
        const nonDisabled = provinces.filter(cb=>!cb.disabled);
        const checkedNonDisabled = nonDisabled.filter(cb=>cb.checked);

        regionCb.checked = checkedNonDisabled.length > 0;
        regionCb.disabled = nonDisabled.length === 0;
    });
}
function initRegionCheckboxes() { syncRegionCheckboxes(); }
</script>
@endpush

<!-- 6. Province Checkboxes & Modal Logic -->
@push('scripts')
<script>
function updateCheckboxesForSide(isRight) {
    const regionsInput = document.getElementById('regionsProvincesInput');
    const side = String(isRight).trim(); // normalize side

    document.querySelectorAll('.province-checkbox').forEach(cb => {
        const code = cb.value.replace('province:', '');

        // Disable if *someone else* already owns this province **on the same side**
        const sideOwners = (window.provinceOwnership[code] || []).filter(p =>
            String(p.isRight).trim() === side &&
            String(p.person_id) !== String(window.currentPersonId)
        );

        // province is locked if owned on this side
        cb.disabled = sideOwners.length > 0;

        // checked if currently selected in modal AND not disabled
        cb.checked = !cb.disabled && window.selectedProvincesPerSide[side].includes(code);
    });

    syncRegionCheckboxes();

    if (regionsInput) {
        regionsInput.value = JSON.stringify(
            Array.from(document.querySelectorAll('.province-checkbox:checked'))
                .map(cb => cb.value.replace('province:', ''))
        );
    }

    const label = document.getElementById('signatorySideLabel');
    if (label) label.textContent = side === '1' ? 'Right side provinces' : 'Left side provinces';
}

function openPersonModal(button) {
    const modal = document.getElementById('addPersonModal');
    if (!modal) return;
    const form = modal.querySelector('form');
    if (!form) return;
    const title = button.getAttribute("data-title") || "Add New/Edit Person";
    const modalTitle = modal.querySelector('.modal-title');
    if (modalTitle) {
        modalTitle.textContent = title+" Signatory";
    }
    
    // Reset form
    form.querySelectorAll('input,select,textarea').forEach(el => {
        const name = el.getAttribute('name') || '';
        if (['person_id','_token','_method'].includes(name)) return;
        if (el.type === 'checkbox' || el.type === 'radio') el.checked = false;
        else if (el.tagName.toLowerCase() === 'select') el.selectedIndex = 0;
        else el.value = '';
    });

    const id = button.dataset.personId || '';
    window.currentPersonId = id;
    const side = button.dataset.isRight ?? '0';

    let provinces = [];
    try { provinces = JSON.parse(button.dataset.provinces || '[]'); } catch(e) { provinces = []; }

    // initialize sides storage fresh
    window.selectedProvincesPerSide = { '0': [], '1': [] };
    window.ownerProvinces = [];

    // preload person’s provinces into the proper side
    provinces.forEach(c => {
        window.selectedProvincesPerSide[side].push(c);
        window.ownerProvinces.push({ code: c, isRight: side });

        if (!window.provinceOwnership[c]) window.provinceOwnership[c] = [];
        const exists = window.provinceOwnership[c].find(p => p.person_id == id && p.isRight == side);
        if (!exists) {
            window.provinceOwnership[c].push({ person_id: id, isRight: side });
        }
    });

    const datasetMap = {
        person_id: 'personId',
        honorific_prefix: 'honorificPrefix',
        complete_name: 'completeName',
        post_nominal: 'postNominal',
        sex: 'sex',
        position_id: 'positionId',
        cell_number: 'cellNumber',
        email: 'email',
        is_right: 'isRight'
    };

    Object.entries(datasetMap).forEach(([fieldName, dsKey]) => {
        const el = form.querySelector(`[name="${fieldName}"]`);
        if (!el) return;
        el.value = (button.dataset[dsKey] !== undefined) ? button.dataset[dsKey] : '';
    });

    // Auto-select position dropdown
    const positionSelect = form.querySelector('select[name="position_id"]');
    if (positionSelect) {
      const positionId = button.dataset.positionId || '';
      if (positionSelect.querySelector(`option[value="${positionId}"]`)) {
        positionSelect.value = positionId;
      } else {
        positionSelect.selectedIndex = 0; // fallback to placeholder
      }
    }
    // Always clear the free-text position input
    const positionNameInput = form.querySelector('input[name="position_name"]');
    if (positionNameInput) positionNameInput.value = '';

    const isRightSelect = form.querySelector('select[name="is_right"]');
    if (isRightSelect) {
        isRightSelect.value = side;
        updateCheckboxesForSide(side); // render the provinces for this side
    }

    syncRegionCheckboxes();
    // sync into hidden input
    liveSyncProvinces();

    modalStack.push('addPersonModal');
    modal.style.display = 'block';
}

function liveSyncProvinces() {
    const provincesInput = document.getElementById('regionsProvincesInput');
    const selected = Array.from(document.querySelectorAll('.province-checkbox:checked'))
        .map(cb => cb.value.replace('province:', ''));
    provincesInput.value = JSON.stringify(selected);
}

// Init
function initRegionCheckboxes() {
    document.querySelectorAll('.region-checkbox').forEach(regionCb => {
        const regionCode = regionCb.dataset.region;
        const provinces = Array.from(document.querySelectorAll(`.province-checkbox[data-region="${regionCode}"]`));
        const nonDisabled = provinces.filter(cb => !cb.disabled);
        const checkedNonDisabled = nonDisabled.filter(cb => cb.checked);

        regionCb.checked = checkedNonDisabled.length > 0;
        regionCb.disabled = nonDisabled.length === 0;
    });
}

document.querySelectorAll('.region-checkbox').forEach(regionCb => {
    regionCb.addEventListener('change', function() {
        const regionCode = this.dataset.region;
        const provinces = document.querySelectorAll(`.province-checkbox[data-region="${regionCode}"]`);
        const side = document.querySelector('select[name="is_right"]').value;

        provinces.forEach(cb => {
            if (!cb.disabled) cb.checked = regionCb.checked;
        });

        window.selectedProvincesPerSide[side] = Array.from(document.querySelectorAll('.province-checkbox:checked'))
            .map(cb => cb.value.replace('province:', ''));

        updateCheckboxesForSide(side);
    });
});

document.querySelectorAll('.province-checkbox').forEach(cb => {
    cb.addEventListener('change', function() {
        const side = document.querySelector('select[name="is_right"]').value;
        window.selectedProvincesPerSide[side] = Array.from(document.querySelectorAll('.province-checkbox:checked'))
            .map(cb => cb.value.replace('province:', ''));
        updateCheckboxesForSide(side);
    });
});
</script>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {

    const provinceModal = document.getElementById('provinceTaggerModal');

    if(!provinceModal) return;
    provinceModal.addEventListener('click', function(e) {
      if (e.target === provinceModal) closeModal('provinceTaggerModal');
    });

});
</script>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isRightSelect = document.querySelector('select[name="is_right"]');
    if (isRightSelect) {
        isRightSelect.addEventListener('change', function() {
            updateCheckboxesForSide(this.value); // restore when switching back
        });
    }
});
</script>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') {
        if (modalStack.length > 0) {

          const topModalId = modalStack[modalStack.length - 1];
          closeModal(topModalId);
        }
      }
    });
});

</script>
@endpush