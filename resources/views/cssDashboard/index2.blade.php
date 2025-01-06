<?php $qr_side = "active"; $qr_home="active"?>

@extends('layouts.index')

@section('styles')
  <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/assets/iCheck/skins/flat/green.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">
  {{-- <link rel="stylesheet" href="{{ asset('public/css/xmas.css') }}"> --}}
  {{-- <link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}"> --}}
  <style>
    *{
      scroll-behavior: smooth;
    }
    /* My custom shadows */
    .shadow-sm	{box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);}
    .shadow	    {box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);}
    .shadow-md	{box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);}
    .shadow-lg	{box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);}
    .shadow-xl	{box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);}
    .shadow-2xl	{box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25);}
    .shadow-inner	{box-shadow: inset 0 2px 4px 0 rgb(0 0 0 / 0.05);}
    .shadow-none	{box-shadow: 0 0 #0000;}


    .mt-4{
      margin-top: 4rem;
    }

    .trychartt{
      padding: 0;
      display: flex;
      color: white;
      border-radius: 10px;
      overflow: hidden;
      width: 100%;
      font-size: 1rem;
    }

    .yes{
      background: rgb(57, 158, 57);
      color: white;
    }

    .no{
      background: rgb(192, 79, 79);
      color: white;
    }

    .bar{
      padding: 0.4rem;
      display: grid;
      place-items: center;
    }

    .title{
      position: absolute;
      top: 0.4em;
      left: 6rem;
      font-size: 2rem;
      font-weight: 700;
    }

    .banner_items{
      position: static;
      z-index: 99999;
      display: grid;
      grid-template: 'one two three';
      grid-template-columns: 3fr 3fr 3fr;
      gap: 5vw;
    }

    .nav_menu{
      border-bottom: none;
    }

    .banner_item{
      
    }

    .one{
      grid-area: one;
    }
    .two{
      grid-area: two;
      background: #bdbdbd60;
      border-radius: 12px;
      display: flex;
      padding: 0.2rem;
      justify-content: space-evenly;
      align-items: center;
      overflow: hidden;
    }
    .three{
      grid-area: three;
    }

    .selector[selected]{
      background: white;
      color: black;
      border-radius: 10px;
      /* width:; */
      padding: 0.3rem;
      width: 50%;
      box-shadow: 3px 2px 2px 0 rgb(0 0 0 / 0.05);
      transition: width 1s ease-in-out;
      animation: fadein 0.6s ease-in-out forwards;
      /* transform-origin: left; */
      overflow: hidden;
    }

    .selector{
      width: 50%;
      display: flex;
      justify-content: center;
      cursor: pointer;
      font-weight: 700;
      overflow: hidden;
    }

    .selector:not([selected]):hover{
      color: #4f4f4f;
    }

    .selector[disabled]{
      cursor: not-allowed;
    }
    /* #munSelect:disabled {
      cursor: not-allowed;
    } */
    @keyframes fadein{
      0%{
        /* width: 45%; */
        opacity: 0;
      }
      100%{
        opacity: 1;
        /* width: 50%; */
      }
    }

    .__contents{
      position: relative;
    }

    .bep_content, .conv_content{
      position: absolute;
      left: 0;
      right: 0;
      top: 0;
      bottom: 0;
    }

    .section{
      font-size: 2rem;
    }

    .section[active]{

    }

    .section:not([active]){
      display: none;
    }

    .slideFromRight{
      animation: slideFromRight 0.6s ease-in-out forwards;
    }
    .slideFromRight2{
      animation: slideFromRight 0.6s 0.2s ease-in-out forwards;
    }
    .slideFromRight3{
      animation: slideFromRight 0.6s 0.4s ease-in-out forwards;
    }
    .slideFromRight4{
      animation: slideFromRight 0.6s 0.6s ease-in-out forwards;
    }

    .slideFromLeft{
      animation: slideFromLeft 0.6s ease-in-out forwards;
    }

    @keyframes slideFromRight{
      0%{
        opacity: 0;
        transform: translateX(2rem);
      }
      100%{
        opacity: 1;
        transform: translateX(0rem);
      }
    }

    @keyframes slideFromLeft{
      0%{
        opacity: 0;
        transform: translateX(-2rem);
      }
      100%{
        opacity: 1;
        transform: translateX(0rem);
      }
    }

    .actual_content{
      /* margin: auto; */
      /* height: 100vh; */
      width: 100%;
      min-height: 60vh;
      /* background: white; */
      border-top: 1px solid rgba(170, 170, 170, 0.463);
      padding: 3rem 4rem;
      position: relative;
    }

    .question_cont{
      height: 5rem;
    }

    .percentages > div{
      border-left: 1px solid #ccc;
      flex-grow: 1;
      flex-basis: 0;
    }

    .percentages > div:nth-of-type(1){
      border-left: none;
    }

    .positive{
      color: rgb(0, 163, 0);
    }

    .negative{
      color: rgb(255, 96, 96);
    }

    .indiv_qs{
      transition: all 0.2s ease-in-out;
    }
    .indiv_qs:hover{
      transform: scale(1.05);
      /* outline: #4f4f4f solid 1px; */
      box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25);
    }

    .question_cont{
      display: flex;
      align-items: center;
      font-size: 1.4rem;
      font-weight: 700;
      background-color: rgba(96, 96, 96, 0.059);
      border-radius: 10px;
      padding: 1rem;
      transform: translateY(1rem);
      /* height: 4rem; */
    }

    #agebrack{
      position: relative;
      overflow: hidden;
    }

    #filterFetcher, #filterFetcherConv{
      display: none;
    }

    .fetcher{
      position: absolute;
      display: flex;
      align-items: center;
      gap: 0.2rem;
      justify-content: center;
      font-weight: 700;
      font-size: 1rem;
      color: white;
      left: 0;
      right: 0;
      top: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.3);
      opacity: 1;
      transform: translateY(0);
      transition: opacity 1s ease-in-out, transform 0.6s cubic-bezier(0, 0.81, 0.19, 1.01);
    }

    .gone{
      transform: translateY(-100%);
      opacity: 0;
    }

    .loadingDots{
      width: 1rem;
      background: white;
      border-radius: 100vw;
      aspect-ratio: 1;
    }

    .fetcher > div:nth-of-type(1){
      animation: loadots 1s cubic-bezier(.73,-0.37,.21,1.69) infinite;
    }
    .fetcher > div:nth-of-type(2){
      animation: loadots 1s 0.1s cubic-bezier(.73,-0.37,.21,1.69) infinite;
    }
    .fetcher > div:nth-of-type(3){
      animation: loadots 1s 0.2s cubic-bezier(.73,-0.37,.21,1.69) infinite;
    }

    @keyframes loadots{
      0%, 100%{
        transform: translateY(0rem);
      }
      50%{
        transform: translateY(-1rem);
      }
    }


    
        /* width */
    ::-webkit-scrollbar {
      width: 5px;
    }

    /* Track */
    ::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 10px;
    }

    /* Handle */
    ::-webkit-scrollbar-thumb {
      background: #888;
      border-radius: 10px;
    }

    /* Handle on hover */
    ::-webkit-scrollbar-thumb:hover {
      background: #555;
    }

    /* MEDIA QUERIES */

    @media only screen and (min-width: 1920px){
      .headings{
        grid-template-columns: 1fr 1fr 1fr 1fr 1fr!important;
      }
    }

    /* @media only screen and (max-width: 981px){
      .headings{
        grid-template-columns: 1fr 1fr 1fr!important;
        font-size: 0.8rem!important;
      }
    } */


    /* END MEDIA QUERIES */

    .easteregg{
      position: fixed;
      font-weight: 700;
      font-size: 1.4rem;
      top: 0;
      left: 50%;
      transform: translateX(-50%) translateY(-250%) rotate(15deg);
      transition: all 0.5s cubic-bezier(.73,-0.37,.21,1.69);
      opacity: 1;

      background-color: rgb(86, 166, 86);
      color: rgb(255, 255, 255);
      padding: 1rem;
      border-radius: 10px;
      z-index: 10000000;
      box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25);
      overflow: hidden;
    }

    .easteregg::after{
      content: "";
      position: absolute;
      background: red;
      width: 50px;
      height: 20px;
      border-radius: 2px;
      transform: translateX(-60%) translateY(-80%) rotate(30deg);
      z-index: -1;
      border-bottom: 2px white solid;
    }

    .easteregg:hover{
      opacity: 0.4;
      /* pointer-events: none; */
    }

    .showeasteregg{
      transform: translateX(-50%) translateY(50%);
    }

    #jiggle-bell{
      animation: jiggle 0.2s 5s linear infinite;
    }

    @keyframes jiggle{
      0%, 100%{
        transform: rotate(-5deg);
        transform-origin: top;
        /* filter: blur(1px); */
      }
      50%{
        transform: rotate(5deg);
        transform-origin: top;
        /* filter: blur(2px); */
      }
    }

    .blurDef{
      filter: blur(50px);
      transition: filter 0.4s ease-in-out;
    }

    .blurIn{
      filter: blur(50px);
      animation: blurIn 0.6s ease-in-out forwards;
    }

    @keyframes blurIn{
      0%{
        filter: blur(50px);
      }
      100%{
        filter: blur(0px);
      }
    }
    

    /* lineclamps */
    .line-clamp-1 {
      display: -webkit-box;
      -webkit-line-clamp: 1;
      -webkit-box-orient: vertical;  
      overflow: hidden;
    }
    .line-clamp-2 {
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;  
      overflow: hidden;
    }
    .line-clamp-3 {
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;  
      overflow: hidden;
    }
  </style>
@endsection

@section('content')
{{-- <div class="easteregg">
  <span><i id="jiggle-bell" class="fa fa-bell" aria-hidden="true"></i> Happy Holidays {{Auth::user()->firstName}}!</span>
</div> --}}
<div class="___content" style="position: relative; padding-top: 10rem; margin-bottom: 10rem; height: max-content;">
  {{-- <div class="trychartt shadow-md" style="width: 30rem; background: #808080;"> 
    <div class="bar q1y yes" >Yes</div>
    <div class="bar q1m" >Maybe</div>
    <div class="bar q1n no" >No</div>
  </div> --}}
  <div class="title">
    CSS Dashboard for BeP, Conventional & NRP Survey
  </div>

  <div class="banner_items">
    <div class="one banner_item">

    </div>
    <div class="two banner_item">
      <div id='bep_section' class="selector" selected>
        Binhi e-Padala
      </div>
      <div id='con_section' class="selector">
        Conventional
      </div>
      <div id='nrp_section' class="selector" disabled>
        NRP
      </div>
    </div>
    <div class="three banner_item">

    </div>  
  </div>

  <div class="__contents mt-4">
    <div class="section bep_content slideFromRight" active>
      <strong>Binhi e-Padala Seeds Distribution Survey Results</strong>
        <div class="actual_content">
          <div class="headings" style="display: grid; grid-template-columns: 3fr 3fr 3fr 2fr; gap: 2rem;">

            <!-- <div class="_card1 slideFromRight" style="opacity: 0; display: flex; gap: 1.2em; background: rgba(143, 201, 255, 0.286); border-radius: 20px; padding: 0.4rem 1.2rem 0.4rem 0.4rem; overflow: hidden;">
              <div id="filterFetcherGenderBep" class="fetcher">
                <div class="loadingDots"></div>
                <div class="loadingDots"></div>
                <div class="loadingDots"></div>
              </div>
              <div class="logo" style="aspect-ratio: 1; font-size: 3rem; display: flex; align-items: center; justify-content: center; background:rgba(166, 150, 255, 0.308); padding: 1em; border-radius: 20px;">
                <i class="fa fa-users" aria-hidden="true"></i>
              </div>
              <div class="labels" style="gap: 0;display: flex; align-items: start; justify-content: space-between; flex-direction: column; overflow: hidden;">
                <span style="font-size: 1.6rem; font-weight: 700;">Respondents</span>
                <div style="font-size: 1.4rem; font-weight: 700;">{{$s_questions[0]['total_response']}} <span style="font-weight: 500; font-size: 1rem">Total Respondents</span></div>
                <div style="font-size: 1.4rem; font-weight: 700;"><span id="femalePercBep">{{$femalePerc}}</span>% <span style="font-weight: 500; font-size: 1rem">Female</span></div>
                <div style="font-size: 1.4rem; font-weight: 700;"><span id="malePercBep">{{$malePerc}}</span>% <span style="font-weight: 500; font-size: 1rem">Male</span></div>
              </div>
            </div>

            <div id="agebrack" class="_card1 slideFromRight2" style="opacity: 0; display: flex; gap: 1.2em; background: rgba(255, 207, 141, 0.286); border-radius: 20px; padding: 0.4rem 1.2rem 0.4rem 0.4rem;">
              <div id="ageBracketFetcher" class="fetcher">
                <div class="loadingDots"></div>
                <div class="loadingDots"></div>
                <div class="loadingDots"></div>
              </div>
              <div class="logo" style="aspect-ratio: 1; font-size: 3rem; display: flex; align-items: center; justify-content: center; background:rgba(255, 167, 67, 0.308); padding: 1em; border-radius: 20px;">
                <i class="fa fa-child" aria-hidden="true"></i>
              </div>
              <div class="labels" style="gap: 0;display: flex; align-items: start; justify-content: space-between; flex-direction: column; overflow: hidden;">
                <span style="font-size: 1.6rem; font-weight: 700;">Age Brackets</span>
                <div style="font-size: 1.4rem; font-weight: 700;"><span id="least">0</span>% <span style="font-weight: 500; font-size: 1rem">18-29 y/o</span></div>
                <div style="font-size: 1.4rem; font-weight: 700;"><span id="middle">0</span>% <span style="font-weight: 500; font-size: 1rem">30-59 y/o</span></div>
                <div style="font-size: 1.4rem; font-weight: 700;"><span id="last">0</span>% <span style="font-weight: 500; font-size: 1rem">60+ y/o</span></div>
              </div>
            </div> -->
            
            <div class="_card1 slideFromRight3" style="opacity: 0; display: flex; gap: 1.2em; background: rgba(112, 213, 137, 0.286); border-radius: 20px; padding: 0.4rem 1.2rem 0.4rem 0.4rem;">
              <div class="logo" style="aspect-ratio: 1; font-weight: 900; font-size: 3rem; display: flex; align-items: center; justify-content: center; background:rgba(0, 183, 89, 0.308); padding: 1em; border-radius: 20px;">
                {{count($survey_questions)}}
              </div>
              <div class="labels" style="gap: 0;display: flex; align-items: start; justify-content: center; flex-direction: column; overflow: hidden;">
                <div style="font-size: 1.6rem; font-weight: 900;">Total Questions</div>
              </div>
            </div>

          </div>
          <hr>
          <div class="head_title_content" style="border-radius: 10px; position: relative; margin-bottom: 1em; display: flex; justify-content: space-between; padding: 1em; overflow: hidden;">
            <div id="filterFetcher" class="fetcher">
              <div class="loadingDots"></div>
              <div class="loadingDots"></div>
              <div class="loadingDots"></div>
            </div>
            <div style="display: flex; gap: 1rem;">
              <h2><strong>Results from</strong></h2>
              <select id="prvSelect" class="form-select" aria-label="provinces" style="border-radius: 5px!important; font-size: 1.6rem; border: none;">
                <option value="All">All Provinces</option>
              </select>
              <select id="munSelect" class="form-select" aria-label="municipalities" style="border-radius: 5px!important; font-size: 1.6rem; border: none;">
                <option value="All">All Municipalities</option>
              </select>
              <h2>with <span id="totalBep" style="font-weight: 700;">{{$s_questions[0]['total_response']}}</span> respondents</h2>
            </div>

            <div class="div">
              <button id="exportbep" class="btn btn-success" style="float: right;"><i style="font-weight: 300;" class="fa fa-file-excel-o" aria-hidden="true"></i> Export Computed Results</button>
              <button id="exportbepraw" class="btn btn-primary" style="float: right;"><i style="font-weight: 300;" class="fa fa-file-excel-o" aria-hidden="true"></i> Export Raw Data</button>
              
            </div>
            
          </div>
          <div class="cards_questions shadow-inner" style="height: 55vh; display: grid; grid-template-columns: 1fr 1fr ; gap: 1em; overflow-y: auto; overflow-x: hidden; border-radius: 10px; padding: 1em;">
            
            <!-- <div class="_cardq shadow-none" style="background: rgba(130, 161, 187, 0.064); height: 20rem; border-radius: 10px; padding: 0;">
              <h5 style="margin: 1em 1em 0 1em; height: 4rem;"><strong>Gusto mo bang magpatuloy sa Binhi e-Padala na sistema ng pamimigay ng binhi?</strong></h5>
              <hr>
              <div class="percentages" style="display: flex; gap: 1em; align-items: center; justify-content: space-evenly;">
                <div style="font-size: 2.4rem; font-weight: 700; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                  <div>64%</div>
                  <span class="positive" style="font-size: 1.2rem; background: rgba(63, 255, 63, 0.184); padding: 0.2em 0.4em; border-radius: 10px;">answered YES</span>
                </div>
                <div style="font-size: 2.4rem; font-weight: 700; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                  <div>20%</div> 
                  <span class="negative" style="font-size: 1.2rem; background: rgba(255, 86, 86, 0.184); padding: 0.2em 0.4em; border-radius: 10px;">answered NO</span>
                </div>
                <div style="font-size: 2.4rem; font-weight: 700; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                  <div>16%</div>
                  <span style="font-size: 1.2rem; background: rgba(182, 182, 182, 0.184); padding: 0.2em 0.4em; border-radius: 10px;">no answer</span>
                </div>
              </div>
            </div>

            <div class="_cardq shadow-none" style="background: rgba(130, 161, 187, 0.064); height: 20rem; border-radius: 10px; padding: 0;">
              <h5 style="margin: 1em 1em 0 1em; height: 4rem;"><strong>Nalaman ko ng mas maaga ang iskedyul dahil sa text.</strong></h5>
              <hr>
              <div class="percentages" style="display: flex; gap: 1em; align-items: center; justify-content: space-evenly;">
                <div style="font-size: 2.4rem; font-weight: 700; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                  <div>24%</div>
                  <span class="positive" style="font-size: 1.2rem; background: rgba(63, 255, 63, 0.184); padding: 0.2em 0.4em; border-radius: 10px;">agrees</span>
                </div>
                <div style="font-size: 2.4rem; font-weight: 700; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                  <div>20%</div>
                  <span style="font-size: 1.2rem; background: rgba(182, 182, 182, 0.184); padding: 0.2em 0.4em; border-radius: 10px;">neutral</span>
                </div>
                <div style="font-size: 2.4rem; font-weight: 700; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                  <div>16%</div>
                  <span class="negative" style="font-size: 1.2rem; background: rgba(255, 86, 86, 0.184); padding: 0.2em 0.4em; border-radius: 10px;">disagrees</span>
                </div>
                <div style="font-size: 2.4rem; font-weight: 700; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                  <div>36%</div>
                  <span style="font-size: 1.2rem;  background: rgba(182, 182, 182, 0.184); padding: 0.2em 0.4em; border-radius: 10px;">no answer</span>
                </div>
              </div>
            </div>

            <div class="_cardq shadow-none" style="background: rgba(130, 161, 187, 0.064); height: 20rem; border-radius: 10px; padding: 0;">
              <h5 style="margin: 1em 1em 0 1em; height: 4rem;"><strong>Gusto mo bang magpatuloy sa Binhi e-Padala na sistema ng pamimigay ng binhi?</strong></h5>
              <hr>
              <div class="percentages" style="display: flex; gap: 1em; align-items: center; justify-content: space-evenly;">
                <div style="font-size: 2.4rem; font-weight: 700; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                  <div>64%</div>
                  <span class="positive" style="font-size: 1.2rem; background: rgba(63, 255, 63, 0.184); padding: 0.2em 0.4em; border-radius: 10px;">answered YES</span>
                </div>
                <div style="font-size: 2.4rem; font-weight: 700; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                  <div>20%</div> 
                  <span class="negative" style="font-size: 1.2rem; background: rgba(255, 86, 86, 0.184); padding: 0.2em 0.4em; border-radius: 10px;">answered NO</span>
                </div>
                <div style="font-size: 2.4rem; font-weight: 700; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                  <div>16%</div>
                  <span style="font-size: 1.2rem; background: rgba(182, 182, 182, 0.184); padding: 0.2em 0.4em; border-radius: 10px;">no answer</span>
                </div>
              </div>
            </div> -->

            <!-- @foreach($perc_questions as $row)
              @if($row['type'] == 'spec')
                <a href="#container">
                  <div class="indiv_qs _cardq shadow-none" style="background: rgba(130, 161, 187, 0.064); height: 20rem; border-radius: 10px; padding: 0; cursor: pointer;" data-data="{{$row['id']}}" data-qs="{{$row['qs']}}">
                    <div class="question_cont" style="margin: 1em; color: black;">{{$row['qs']}}</div>
                    <hr>
                    <div class="percentages" style="padding: 1rem; display: flex; gap: 1em; align-items: center; justify-content: space-evenly;">
                      <div style="font-size: 2rem; font-weight: 700; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <div id="{{$row['id']}}_agree">{{$row['agree']}}%</div>
                        <span class="positive line-clamp-1" style="font-size: 1.2rem; background: rgba(63, 255, 63, 0.184); padding: 0.2em 0.4em; border-radius: 10px;">agrees</span>
                      </div>
                      <div style="font-size: 2rem; font-weight: 700; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <div id="{{$row['id']}}_neutral">{{$row['neutral']}}%</div>
                        <span class="line-clamp-1" style="font-size: 1.2rem; background: rgba(182, 182, 182, 0.184); padding: 0.2em 0.4em; border-radius: 10px;">neutral</span>
                      </div>
                      <div style="font-size: 2rem; font-weight: 700; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <div id="{{$row['id']}}_disagree">{{$row['disagree']}}%</div>
                        <span class="negative line-clamp-1" style="font-size: 1.2rem; background: rgba(255, 86, 86, 0.184); padding: 0.2em 0.4em; border-radius: 10px;">disagrees</span>
                      </div>
                      <div style="font-size: 2rem; font-weight: 700; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <div id="{{$row['id']}}_none">{{$row['none']}}%</div>
                        <span class="line-clamp-1" style="font-size: 1.2rem;  background: rgba(182, 182, 182, 0.184); padding: 0.2em 0.4em; border-radius: 10px;">no answer</span>
                      </div>
                    </div>
                  </div>
                </a>
              @else
                <a href="#container">
                  <div class="indiv_qs _cardq shadow-none" style="background: rgba(130, 161, 187, 0.064); height: 20rem; border-radius: 10px; padding: 0; cursor: pointer;" data-data="{{$row['id']}}" data-qs="{{$row['qs']}}">
                    <div class="question_cont" style="margin: 1em; color: black">{{$row['qs']}}</div>
                    <hr>
                    <div class="percentages" style="padding: 1rem; display: flex; gap: 1em; align-items: center; justify-content: space-evenly;">
                      <div style="font-size: 2rem; font-weight: 700; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <div id="{{$row['id']}}_yes">{{$row['yes']}}%</div>
                        <span class="positive line-clamp-1" style="font-size: 1.2rem; background: rgba(63, 255, 63, 0.184); padding: 0.2em 0.4em; border-radius: 10px;">answered YES</span>
                      </div>
                      <div style="font-size: 2rem; font-weight: 700; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <div id="{{$row['id']}}_no">{{$row['no']}}%</div> 
                        <span class="negative line-clamp-1" style="font-size: 1.2rem; background: rgba(255, 86, 86, 0.184); padding: 0.2em 0.4em; border-radius: 10px;">answered NO</span>
                      </div>
                      <div style="font-size: 2rem; font-weight: 700; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <div id="{{$row['id']}}_maybe">{{$row['maybe']}}%</div>
                        <span class="line-clamp-1" style="font-size: 1.2rem; background: rgba(182, 182, 182, 0.184); padding: 0.2em 0.4em; border-radius: 10px;">no answer</span>
                      </div>
                    </div>
                  </div>
                </a>
              @endif
            @endforeach -->
          
            @foreach($s_questions as $key => $question)
              @if($question['type'] == 'yesno')
                <!-- <a href="#container"> -->
                  <div class="indiv_qs _cardq shadow-none" style="background: rgba(130, 161, 187, 0.064); height: 20rem;  border-radius: 10px; padding: 0; cursor: pointer;" data-data="{{$question['id']}}" data-qs="{{$question['question']}}">
                    <div class="question_cont" style="margin: 1em; color: black;">{{$question['id']}}. {{ $question['question'] }}</div>
                    <hr>
                    <div class="percentages" style="padding: 1rem; display: flex; gap: 1em; align-items: center; justify-content: space-evenly;">
                      @foreach($question['options'] as $loopIndex => $option)
                        @php
                            $backgroundColor = $loopIndex == 1 ? 'rgba(225, 114, 114, 0.184)' : 'rgba(63, 255, 63, 0.184)';
                            $class = $loopIndex == 1 ? 'negative' : 'positive';
                            $percentage = ($option['count']/$question['total_response'])* 100;
                        @endphp
                        <!-- <div style="font-size: 2rem; font-weight: 700; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                            <div id="{{$row['id']}}_agree"></div>
                            <span class="{{ $class }} line-clamp-1" style="font-size: 2rem; background: {{ $backgroundColor }}; padding: 0.2em 0.4em; border-radius: 10px;">{{ $option['display'] }}</span>
                            <div class="progress-bar" style="width: {{ $percentage }}%; background: {{ $backgroundColor }}; height: 2rem; border-radius: 10px; position: relative;margin:1em;left: 0;">
                              <span style="position: absolute; left: 50%; top: 150%; transform: translate(-50%, -50%); font-size: 1rem; font-weight: 700; color: #333;">
                                  {{ $option['count'] }}/{{ $total_respoondents_conv }}
                              </span>
                            </div>
                        </div> -->
                        <div style="font-size: 2rem; font-weight: 700; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                              <div id="{{$question['id']}}_agree"></div>
                              <span class="{{ $class }} line-clamp-1" style="font-size: 2rem; background: {{ $backgroundColor }}; padding: 0.2em 0.4em; border-radius: 10px;">{{ $option['display'] }}</span>
                              <div class="progress-bar-container" style="width: 100%; background: #f1f1f1; border-radius: 5px; margin-top: 0.5em; margin-left: 0.5em; position: relative; border: 2px solid {{ $backgroundColor }};">
                                <div id="bep_bar_<?php echo $key; ?>_<?php echo $loopIndex; ?>" class="progress-bar" style="width: {{ $percentage }}%; background: {{ $backgroundColor }}; height: 2rem; border-radius: 5px; position: relative; left: 0;"></div> 
                                <span id="bep_question_<?php echo $key; ?>_<?php echo $loopIndex; ?>" style="position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); font-size: 1.2rem; font-weight: 700; color: (182, 182, 182, 0.184);">
                                {{ $option['count'] }} / {{ $question['total_response'] }}
                                </span>
                              </div>
                              <!-- <div>{{number_format($percentage, 2)}}%</div> -->
                          </div>
                        <!-- <div style="font-size: 2rem; font-weight: 700; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                              <div id="{{$question['id']}}_agree"></div>
                              <span class="{{ $class }} line-clamp-1" style="font-size: 2rem; background: {{ $backgroundColor }}; padding: 0.2em 0.4em; border-radius: 10px;">{{ $option['display'] }}</span>
                              <div class="progress-bar-container" style="width: 100%; background: #f1f1f1; border-radius: 10px; margin-top: 0.5em; position: relative;">
                                  <div class="progress-bar" style="width: {{ $percentage }}%; background: {{ $backgroundColor }}; height: 1rem; border-radius: 10px; position: relative;">
                                      <span style="position: absolute; left: 50%; top: 1.5rem; transform: translateX(-50%); font-size: 1rem; font-weight: 700; color: #333;">
                                          {{ $option['count'] }}/{{ $total_respoondents_conv }}
                                      </span>
                                  </div>
                              </div>
                          </div> -->
                      @endforeach
                    </div>
                  </div>
                <!-- </a> -->
              @else
                <!-- <a href="#container"> -->
                  <div class="indiv_qs _cardq shadow-none" style="background: rgba(130, 161, 187, 0.064); height: 20rem; border-radius: 10px; padding: 0; cursor: pointer;" data-data="{{$question['id']}}" data-qs="{{$question['question']}}">
                    <div class="question_cont" style="margin: 1em; color: black">{{$question['id']}}. {{$question['question']}}</div>
                    <hr>
                    <div class="percentages" style="padding: 1rem; display: flex; gap: 1em; align-items: center; justify-content: space-evenly;">
                        @foreach($question['options'] as $loopIndex => $option)
                          @php
                            if ($loopIndex == 0) {
                                $backgroundColor = 'rgba(63, 255, 63, 0.184)';
                                $class = 'positive';
                            } elseif ($loopIndex == 1) {
                                $backgroundColor = 'rgba(16, 159, 64, 0.184)'; 
                                $class = 'positive';
                            } elseif ($loopIndex == 2) {
                                $backgroundColor = 'rgba(225, 114, 114, 0.184)'; 
                                $class = '';
                            } elseif ($loopIndex == 3) {
                                $backgroundColor = 'rgba(235, 75, 75, 0.184)'; 
                                $class = 'negative';
                            } elseif ($loopIndex == 4) {
                                $backgroundColor = 'rgba(235, 75, 75, 0.184)'; 
                                $class = 'negative';
                            } else {
                                $backgroundColor = 'rgba(182, 182, 182, 0.184)';
                                $class = '';
                            }

                            
                            $percentage = ($option['count']/$question['total_response'])* 100;
                          @endphp
                          <div style="font-size: 2rem; font-weight: 700; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                              <div id="{{$row['id']}}_agree"></div>
                              <span class="{{ $class }} line-clamp-1" style="font-size: 1.2rem; background: {{ $backgroundColor }}; padding: 0.2em 0.4em; border-radius: 10px;">{{ $option['display'] }}</span>
                              <div class="progress-bar-container" style="width: 100%; background: #f1f1f1; border-radius: 5px; margin-top: 0.5em; margin-left: 0.5em; position: relative; border: 2px solid {{ $backgroundColor }};">
                                <div id="bep_bar_<?php echo $key; ?>_<?php echo $loopIndex; ?>" class="progress-bar" style="width: {{ $percentage }}%; background: {{ $backgroundColor }}; height: 2rem; border-radius: 5px; position: relative; left: 0;"></div> 
                                <span  id="bep_question_<?php echo $key; ?>_<?php echo $loopIndex; ?>" style="position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); font-size: 1.2rem; font-weight: 700; color: (182, 182, 182, 0.184);">
                                {{ $option['count'] }} / {{ $question['total_response'] }}
                                </span>
                              </div>
                              <!-- <div>{{number_format($percentage, 2)}}%</div> -->
                          </div>
                        @endforeach
                      </div>
                  </div>
                <!-- </a> -->
              @endif
            @endforeach

          </div>
          <!-- <figure class="highcharts-figure mt-4 shadow-md" style="padding: 3rem 2rem; border-radius: 10px;">
            <div id="container" style="height: 35rem; position: relative;"></div>
            <p class="highcharts-description" style="font-size: 1.2rem; font-style: italic; margin-top: 1rem;">
                *You may click one of the questions above to load their respective raw data.
                *All of the data above are live. *You can refresh the page to see realtime updates.
            </p>
          </figure> -->
        </div>
    </div>
    <div class="section con_content slideFromRight">
      <strong>Conventional Seeds Distribution Survey Results</strong>
        <div class="actual_content">
          <div class="headings" style="display: grid; grid-template-columns: 3fr 3fr 3fr 2fr; gap: 2rem;">

            <!-- <div class="_card1 slideFromRight2" style="opacity: 0; display: flex; gap: 1.2em; background: rgba(143, 201, 255, 0.286); border-radius: 20px; padding: 0.4rem 1.2rem 0.4rem 0.4rem;">
              <div class="logo" style="aspect-ratio: 1; font-size: 3rem; display: flex; align-items: center; justify-content: center; background:rgba(166, 150, 255, 0.308); padding: 1em; border-radius: 20px;">
                <i class="fa fa-users" aria-hidden="true"></i>
              </div>
              <div class="labels" style="gap: 0;display: flex; align-items: start; justify-content: space-between; flex-direction: column; overflow: hidden;">
                <span style="font-size: 1.6rem; font-weight: 700;">Respondents</span>
                <div style="font-size: 1.4rem; font-weight: 700;">{{$total_respoondents_conv}} <span style="font-weight: 500; font-size: 1rem">Total Respondents</span></div>
                <div style="font-size: 1.4rem; font-weight: 700;">{{$femalePerc}}% <span style="font-weight: 500; font-size: 1rem">Female</span></div>
                <div style="font-size: 1.4rem; font-weight: 700;">{{$malePerc}}% <span style="font-weight: 500; font-size: 1rem">Male</span></div>
              </div>
            </div>

            <div id="agebrack" class="_card1 slideFromRight3" style="opacity: 0; display: flex; gap: 1.2em; background: rgba(255, 207, 141, 0.286); border-radius: 20px; padding: 0.4rem 1.2rem 0.4rem 0.4rem;">
              <div id="ageBracketFetcher" class="fetcher">
                <div class="loadingDots"></div>
                <div class="loadingDots"></div>
                <div class="loadingDots"></div>
              </div>
              <div class="logo" style="aspect-ratio: 1; font-size: 3rem; display: flex; align-items: center; justify-content: center; background:rgba(255, 167, 67, 0.308); padding: 1em; border-radius: 20px;">
                <i class="fa fa-child" aria-hidden="true"></i>
              </div>
              <div class="labels" style="gap: 0;display: flex; align-items: start; justify-content: space-between; flex-direction: column; overflow: hidden;">
                <span style="font-size: 1.6rem; font-weight: 700;">Age Brackets</span>
                <div style="font-size: 1.4rem; font-weight: 700;"><span id="least">0</span>% <span style="font-weight: 500; font-size: 1rem">18-29 y/o</span></div>
                <div style="font-size: 1.4rem; font-weight: 700;"><span id="middle">0</span>% <span style="font-weight: 500; font-size: 1rem">30-59 y/o</span></div>
                <div style="font-size: 1.4rem; font-weight: 700;"><span id="last">0</span>% <span style="font-weight: 500; font-size: 1rem">60+ y/o</span></div>
              </div>
            </div> -->
            
            <div class="_card1 slideFromRight4  " style="opacity: 0; display: flex; gap: 1.2em; background: rgba(112, 213, 137, 0.286); border-radius: 20px; padding: 0.4rem 1.2rem 0.4rem 0.4rem;">
              <div class="logo" style="aspect-ratio: 1; font-weight: 900; font-size: 3rem; display: flex; align-items: center; justify-content: center; background:rgba(0, 183, 89, 0.308); padding: 1em; border-radius: 20px;">
                {{count($survey_questions_con)}}
              </div>
              <div class="labels" style="gap: 0;display: flex; align-items: start; justify-content: center; flex-direction: column; overflow: hidden;">
                <div style="font-size: 1.6rem; font-weight: 900;">Total Questions</div>
              </div>
            </div>

          </div>
          <hr>
          <div class="head_title_content" style="border-radius: 10px; position: relative; margin-bottom: 1em; display: flex; justify-content: space-between; padding: 1em; overflow: hidden;">
            <div id="filterFetcherConv" class="fetcher">
              <div class="loadingDots"></div>
              <div class="loadingDots"></div>
              <div class="loadingDots"></div>
            </div>
            <div style="display: flex; gap: 1rem;">
              <h2><strong>Results from</strong></h2>
              <select id="prvSelectConv" class="form-select" aria-label="provinces" style="border-radius: 5px!important; font-size: 1.6rem; border: none;">
                <option value="All">All Provinces</option>
              </select>
              <select id="munSelectConv" class="form-select" aria-label="municipalities" style="border-radius: 5px!important; font-size: 1.6rem; border: none;">
                <option value="All">All Municipalities</option>
              </select>
              <h2>with <span id="totalConv" style="font-weight: 700;">{{$questions_con[0]['total_response']}}</span> respondents</h2>
            </div>

            <div class="div">
              <button id="exportcon" class="btn btn-success" style="float: right;"><i style="font-weight: 300;" class="fa fa-file-excel-o" aria-hidden="true"></i> Export Computed Results</button>
              {{-- <button id="exportconraw" class="btn btn-primary" style="float: right;"><i style="font-weight: 300;" class="fa fa-file-excel-o" aria-hidden="true"></i> Export Raw Data</button> --}}
            </div>
            
          </div>
          {{-- <div class="placeholder_empty" style="gap: 2em;position: absolute; left: 0; right: 0; bottom: 0; top: 0; display: flex; align-items: center; justify-content: center; flex-direction: column;">
            <div class="icon" style="display: flex; gap: 1rem; font-size: 6rem;"><i class="fa fa-database" aria-hidden="true"></i><i class="fa fa-long-arrow-right" aria-hidden="true"></i><i class="fa fa-times" style="color: rgb(253, 85, 85);" aria-hidden="true"></i></div>
            <div class="msg">
              Conventional has no data yet. Come back later, maybe?
            </div>
          </div> --}}
          <!-- <div class="cards_questions shadow-inner" style="height: 55vh; display: grid; grid-template-columns: 1fr 1fr ; gap: 1em; overflow-y: auto; overflow-x: hidden; border-radius: 10px; padding: 1em;"> -->
            <!-- @foreach($perc_questions_conv as $row)
              @if($row['type'] == 'spec')
                <a href="#containerConv">
                  <div class="indiv_qs shadow-none convClick" style="background: rgba(130, 161, 187, 0.064); height: 20rem; border-radius: 10px; padding: 0; cursor: pointer;" data-data="{{$row['id']}}" data-qs="{{$row['qs']}}">
                    <div class="question_cont" style="margin: 1em; color: black;">{{$row['qs']}}</div>
                    <hr>
                    <div class="percentages" style="padding: 1rem; display: flex; gap: 1em; align-items: center; justify-content: space-evenly;">
                      <div style="font-size: 2rem; font-weight: 700; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <div id="{{$row['id']}}_yes_2">{{$row['yes_2']}}%</div>
                        <span class="positive line-clamp-1" style="font-size: 1.2rem; background: rgba(63, 255, 63, 0.184); padding: 0.2em 0.4em; border-radius: 10px;">Strongly Agree</span>
                      </div>
                      <div style="font-size: 2rem; font-weight: 700; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <div id="{{$row['id']}}_yes_1">{{$row['yes_1']}}%</div>
                        <span class="positive line-clamp-1" style="font-size: 1.2rem; background: rgba(16, 159, 64, 0.184); padding: 0.2em 0.4em; border-radius: 10px;">Agree</span>
                      </div>
                      <div style="font-size: 2rem; font-weight: 700; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <div id="{{$row['id']}}_neutral">{{$row['neutral']}}%</div>
                        <span class="line-clamp-1" style="font-size: 1.2rem; background: rgba(255, 86, 86, 0.184); padding: 0.2em 0.4em; border-radius: 10px;">Neutral</span>
                      </div>
                      <div style="font-size: 2rem; font-weight: 700; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <div id="{{$row['id']}}_no_1">{{$row['no_1']}}%</div>
                        <span class="negative line-clamp-1" style="font-size: 1.2rem;  background: rgba(225, 114, 114, 0.184); padding: 0.2em 0.4em; border-radius: 10px;">Disagree</span>
                      </div>
                      <div style="font-size: 2rem; font-weight: 700; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <div id="{{$row['id']}}_no_2">{{$row['no_2']}}%</div>
                        <span class="negative line-clamp-1" style="font-size: 1.2rem;  background: rgba(235, 75, 75, 0.184); padding: 0.2em 0.4em; border-radius: 10px;">Strongly Disagree</span>
                      </div>
                      <div style="font-size: 2rem; font-weight: 700; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <div id="{{$row['id']}}_none">{{$row['none']}}%</div>
                        <span class="line-clamp-1" style="font-size: 1.2rem;  background: rgba(182, 182, 182, 0.184); padding: 0.2em 0.4em; border-radius: 10px;">None</span>
                      </div>
                    </div>
                  </div>
                </a>
              @else
                {{-- <a href="#container">
                  <div class="indiv_qs _cardq shadow-none convClick" style="background: rgba(130, 161, 187, 0.064); height: 20rem; border-radius: 10px; padding: 0; cursor: pointer;" data-data="{{$row['id']}}" data-qs="{{$row['qs']}}">
                    <div class="question_cont" style="margin: 1em; color: black">{{$row['qs']}}</div>
                    <hr>
                    <div class="percentages" style="padding: 1rem; display: flex; gap: 1em; align-items: center; justify-content: space-evenly;">
                      <div style="font-size: 2rem; font-weight: 700; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <div id="{{$row['id']}}_yes">{{$row['yes']}}%</div>
                        <span class="positive line-clamp-1" style="font-size: 1.2rem; background: rgba(63, 255, 63, 0.184); padding: 0.2em 0.4em; border-radius: 10px;">answered YES</span>
                      </div>
                      <div style="font-size: 2rem; font-weight: 700; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <div id="{{$row['id']}}_no">{{$row['no']}}%</div> 
                        <span class="negative line-clamp-1" style="font-size: 1.2rem; background: rgba(255, 86, 86, 0.184); padding: 0.2em 0.4em; border-radius: 10px;">answered NO</span>
                      </div>
                      <div style="font-size: 2rem; font-weight: 700; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <div id="{{$row['id']}}_maybe">{{$row['maybe']}}%</div>
                        <span class="line-clamp-1" style="font-size: 1.2rem; background: rgba(182, 182, 182, 0.184); padding: 0.2em 0.4em; border-radius: 10px;">no answer</span>
                      </div>
                    </div>
                  </div>
                </a> --}}
              @endif
            @endforeach -->
            <!-- <div class="cards_questions shadow-inner" style="height: 55vh; display: grid; grid-template-columns: 1fr  ; gap: 1em; overflow-y: auto; overflow-x: hidden; border-radius: 10px; padding: 1em;"> -->
              @foreach($questions_con as $key => $row)
                <!-- <a href="#containerConv"> -->
                  <div class="indiv_qs shadow-none " style="background: rgba(130, 161, 187, 0.064); height: 20rem; border-radius: 10px; padding: 0; cursor: pointer;" data-data="{{$question['id']}}" data-qs="{{ $question['question'] }}">
                    <div class="question_cont" style="margin: 1em; color: black;">{{$row['id']}}. {{ $row['question'] }}</div>
                    <hr>
                    <div class="percentages" style="padding: 1rem; display: flex; gap: 1em; align-items: center; justify-content: space-evenly;">
                    @foreach($row['options'] as $loopIndex => $option)
                        @php
                          if ($loopIndex == 0) {
                              $backgroundColor = 'rgba(63, 255, 63, 0.184)';
                              $class = 'positive';
                          } elseif ($loopIndex == 1) {
                              $backgroundColor = 'rgba(16, 159, 64, 0.184)'; 
                              $class = 'positive';
                          } elseif ($loopIndex == 2) {
                              $backgroundColor = 'rgba(225, 114, 114, 0.184)'; 
                              $class = '';
                          } elseif ($loopIndex == 3) {
                              $backgroundColor = 'rgba(235, 75, 75, 0.184)'; 
                              $class = 'negative';
                          } elseif ($loopIndex == 4) {
                              $backgroundColor = 'rgba(235, 75, 75, 0.184)'; 
                              $class = 'negative';
                          } else {
                              $backgroundColor = 'rgba(182, 182, 182, 0.184)';
                              $class = '';
                          }
                          try {
                            $percentage = ($option['count']/$row['total_response'])* 100;
                          } catch (\Exception $e) {
                            $percentage = 0;
                          }
                        @endphp
                        <div style="font-size: 2rem; font-weight: 700; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                              <div id="{{$row['id']}}_agree"></div>
                              <span class="{{ $class }} line-clamp-1" style="font-size: 1.2rem; background: {{ $backgroundColor }}; padding: 0.2em 0.4em; border-radius: 10px;">{{ $option['display'] }}</span>
                              <div class="progress-bar-container" style="width: 100%; background: #f1f1f1; border-radius: 5px; margin-top: 0.5em; margin-left: 0.5em; position: relative; border: 2px solid {{ $backgroundColor }};">
                                <div id="con_bar_<?php echo $key; ?>_<?php echo $loopIndex; ?>" class="progress-bar" style="width: {{ $percentage }}%; background: {{ $backgroundColor }}; height: 2rem; border-radius: 5px; position: relative; left: 0;"></div> 
                                <span id="con_question_<?php echo $key; ?>_<?php echo $loopIndex; ?>" style="position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); font-size: 1.2rem; font-weight: 700; color: (182, 182, 182, 0.184);">
                                {{ $option['count'] }} / {{ $row['total_response'] }}
                                </span>
                              </div>
                              <!-- <div>{{number_format($percentage, 2)}}%</div> -->
                          </div>
                    @endforeach
                    
                    </div>
                  </div>
                <!-- </a> -->
              @endforeach
            <!-- </div> -->
            <!-- <figure class="highcharts-figure mt-4 shadow-md" style="padding: 3rem 2rem; border-radius: 10px;">
              <div id="containerConv" style="height: 35rem; position: relative;"></div>
              <p class="highcharts-description" style="font-size: 1.2rem; font-style: italic; margin-top: 1rem;">
                  *You may click one of the questions above to load their respective raw data.
                  *All of the data above are live. *You can refresh the page to see realtime updates.
              </p>
            </figure> -->
        </div>
      </div>
      <div class="section nrp_content slideFromRight">
        <div class="placeholder_empty" style="gap: 2em;position: absolute; left: 0; right: 0; bottom: 0; top: 30rem; display: flex; align-items: center; justify-content: center; flex-direction: column;">
            <div class="icon" style="display: flex; gap: 1rem; font-size: 6rem;"><i class="fa fa-database" aria-hidden="true"></i><i class="fa fa-long-arrow-right" aria-hidden="true"></i><i class="fa fa-times" style="color: rgb(253, 85, 85);" aria-hidden="true"></i></div>
            <div class="msg">
              NRP Survey has no data yet. Come back later, maybe?
            </div>
          </div>
      </div>
    </div>
  </div>
</div>
@endsection()

@push('scripts')
    <script src=" {{ asset('public/js/jquery.inputmask.bundle.js') }} "></script>
    <script src=" {{ asset('public/js/select2.min.js') }} "></script>
    <script src=" {{ asset('public/js/parsely.js') }} "></script>
    <script src=" {{ asset('public/assets/iCheck/icheck.min.js') }} "></script>
    <script src=" {{ asset('public/js/daterangepicker.js') }} "></script>
    <script src=" {{ asset('public/js/highcharts.js') }} "></script>

    <script>
      showEasterEgg();
      loadProvinces();
      loadProvincesConv();
      getGenderBep();
      $yes = 64;
      $no = 16;
      $maybe = 20;
      $total = ($yes + $no) + $maybe;
      
      $('.q1y').css('width', (($yes/$total)*100)+'%');
      $('.q1m').css('width', (($maybe/$total)*100)+'%');
      $('.q1n').css('width', (($no/$total)*100)+'%');

      function getGenderBep(){
        $.ajax({
          type: 'GET',
          url: "getGender/bep",
          data: {
            _token: "{{ csrf_token() }}",
          },
          success: function(source){
            $('#malePercBep').text(source.male);
            $('#femalePercBep').text(source.female);

            $('#filterFetcherGenderBep').addClass('gone');

            setTimeout(() => {
              $('#filterFetcherGenderBep').css('display', 'none');
            }, 1000);
          }
        });
      }


      //CHANGE SECTIONS
      $('#bep_section').on('click', function() {
        $('#con_section').removeAttr('selected');
        $('#nrp_section').removeAttr('selected');
        $('#bep_section').attr('selected', '');
        $('.bep_content').attr('active', '');
        $('.con_content').removeAttr('active');
        $('.nrp_content').removeAttr('active');
      });

      $('#con_section').on('click', function() {
        $('#bep_section').removeAttr('selected');
        $('#nrp_section').removeAttr('selected');
        $('#con_section').attr('selected', '');
        $('.con_content').attr('active', '');
        $('.bep_content').removeAttr('active');
        $('.nrp_content').removeAttr('active');
      });

      $('#nrp_section').on('click', function() {
        $('#bep_section').removeAttr('selected');
        $('#con_section').removeAttr('selected', '');
        $('#nrp_section').attr('selected', '');
        $('.nrp_content').attr('active', '');
        $('.con_content').removeAttr('active', '');
        $('.bep_content').removeAttr('active');
      });
      //END CHANGE SECTIONS

      //START LISTENERS

      $("#sample_listener").on('click', function() {
        $(".placeholder_empty").css('display', 'none');
        $(".actual_exploded_view").css('display', 'block');
      });

      //END LISTENERS
      getAgeBracket();
      function getAgeBracket(){
        $.ajax({
          type: 'GET',
          url: "{{ route('getBdays') }}",
          success: function(source){
            // console.log(source.least);
            $('#least').text(source.least);
            $('#middle').text(source.middle);
            $('#last').text(source.last);
            $('#ageBracketFetcher').addClass('gone');

            setTimeout(() => {
              $('#ageBracketFetcher').css('display', 'none');
            }, 1000);
          }
        });
      }

      $('._cardq').on('click', function() {
        $qs = $(this).data("qs");
        $selected_prv = $("#prvSelect").val();
        $selected_mun = $("#munSelect").val();
        $("#container").removeClass("blurIn");
        $("#container").addClass("blurDef");
        $.ajax({
          type: 'POST',
          url: "{{ route('getIndivQuestion') }}",
          data: {
            _token: "{{ csrf_token() }}",
            question: $(this).data("data"),
            prv: $selected_prv,
            mun: $selected_mun,
          },
          success: function(source){
            // console.log(source);
            if(source.type == "yn"){
              makeYnChart(source, $qs);
            }else{
              // console.log(source);
              makeSpChart(source, $qs);
            }
            $("#container").removeClass("blurDef");
            $("#container").addClass("blurIn");
          }
        });
      });

      $('.convClick').on('click', function() {
        $qs = $(this).data("qs");
        $selected_prv = $("#prvSelectConv").val();
        $selected_mun = $("#munSelectConv").val();
        $("#containerConv").removeClass("blurIn");
        $("#containerConv").addClass("blurDef");
        $.ajax({
          type: 'POST',
          url: "{{ route('getIndivQuestionConv') }}",
          data: {
            _token: "{{ csrf_token() }}",
            question: $(this).data("data"),
            prv: $selected_prv,
            mun: $selected_mun,
          },
          success: function(source){
            console.log(source);
            makeSpConvChart(source, $qs);
            $("#containerConv").removeClass("blurDef");
            $("#containerConv").addClass("blurIn");
          }
        });
      });

      function makeYnChart($source_data, $question){
        // console.log($question);
        Highcharts.chart('container', {
          chart: {
              type: 'bar',
              backgroundColor:'#F7F7F7',
          },
          title: {
              text: 'Question #'+$question,
              align: 'left',
          },
          subtitle: {
              text: 'Out of the '+(($source_data.yes + $source_data.no) + $source_data.maybe)+' respondents,',
              align: 'left'
          },
          xAxis: {
              categories: [
                'Answered Yes',
                'Answered No', 
                'Did not answer'
              ],
              title: {
                  text: "Answers"
              }
          },
          yAxis: {
              min: 0,
              title: {
                  text: 'Beneficiaries',
                  align: 'high'
              },
              labels: {
                  overflow: 'justify'
              }
          },
          tooltip: {
              valueSuffix: ' answer(s)'
          },
          plotOptions: {
              bar: {
                  dataLabels: {
                      enabled: true
                  }
              }
          },
          // legend: {
          //     layout: 'vertical',
          //     align: 'right',
          //     verticalAlign: 'top',
          //     x: -40,
          //     y: 40,
          //     floating: true,
          //     borderWidth: 1,
          //     backgroundColor:
          //         Highcharts.defaultOptions.legend.backgroundColor || '#FFFFFF',
          //     shadow: true
          // },
          credits: {
              enabled: false
          },
          series: [{
              name: "Beneficiaries",
              data: [
                {
                  name: "Aswered Yes",
                  color: "#36ad72",
                  y: $source_data.yes,
                }, 
                {
                  name: "Answered No",
                  color: "#e36b6b",
                  y: $source_data.no
                }, 
                {
                  name: "No Answer",
                  color: "#bdbdbd",
                  y: $source_data.maybe
                }]
          }]
      });
      }

      function makeSpChart($source_data, $question){
        // console.log($question);
        Highcharts.chart('container', {
          chart: {
              type: 'bar',
              backgroundColor:'#F7F7F7',
          },
          title: {
              text: 'Question #'+$question,
              align: 'left',
          },
          subtitle: {
              text: 'Out of the '+(($source_data.agree + $source_data.disagree) + $source_data.neutral + $source_data.none)+' respondents,',
              align: 'left'
          },
          xAxis: {
              categories: [
                'Agreed',
                'Neutral', 
                'Disagreed',
                'Prefer not to say'
              ],
              title: {
                  text: "Answers"
              }
          },
          yAxis: {
              min: 0,
              title: {
                  text: 'Beneficiaries',
                  align: 'high'
              },
              labels: {
                  overflow: 'justify'
              }
          },
          tooltip: {
              valueSuffix: ' answer(s)'
          },
          plotOptions: {
              bar: {
                  dataLabels: {
                      enabled: true
                  }
              }
          },
          // legend: {
          //     layout: 'vertical',
          //     align: 'right',
          //     verticalAlign: 'top',
          //     x: -40,
          //     y: 40,
          //     floating: true,
          //     borderWidth: 1,
          //     backgroundColor:
          //         Highcharts.defaultOptions.legend.backgroundColor || '#FFFFFF',
          //     shadow: true
          // },
          credits: {
              enabled: false
          },
          series: [{
              name: "Benecifiaries",
              data: [
                {
                  name: "Agreed",
                  color: "#36ad72",
                  y: $source_data.agree,
                }, 
                {
                  name: "Neutral",
                  color: "#bdbdbd",
                  y: $source_data.neutral
                }, 
                {
                  name: "Disagreed",
                  color: "#e36b6b",
                  y: $source_data.disagree
                }, 
                {
                  name: "Prefer not to say",
                  color: "#bdbdbd",
                  y: $source_data.none
                }]
          }]
      });
      }

      function makeSpConvChart($source_data, $question){
        // console.log($question);
        Highcharts.chart('containerConv', {
          chart: {
              type: 'bar',
              backgroundColor:'#F7F7F7',
          },
          title: {
              text: 'Question #'+$question,
              align: 'left',
          },
          subtitle: {
              text: 'Out of the '+($source_data.str_disagree + $source_data.str_agree + $source_data.agree + $source_data.disagree + $source_data.neutral + $source_data.none)+' respondents,',
              align: 'left'
          },
          xAxis: {
              categories: [
                'Strongly agree',
                'Agree',
                'Neutral',
                'Strongly disagree',
                'Disagree',
                'Prefer not to say'
              ],
              title: {
                  text: "Answers"
              }
          },
          yAxis: {
              min: 0,
              title: {
                  text: 'Beneficiaries',
                  align: 'high'
              },
              labels: {
                  overflow: 'justify'
              }
          },
          tooltip: {
              valueSuffix: ' answer(s)'
          },
          plotOptions: {
              bar: {
                  dataLabels: {
                      enabled: true
                  }
              }
          },
          credits: {
              enabled: false
          },
          series: [{
              name: "Benecifiaries",
              data: [
                {
                  name: "Strongly agree",
                  color: "#36ad72",
                  y: $source_data.str_agree,
                },{
                  name: "Agree",
                  color: "#36ad72",
                  y: $source_data.agree,
                }, 
                {
                  name: "Neutral",
                  color: "#bdbdbd",
                  y: $source_data.neutral
                },{
                  name: "Disagree",
                  color: "#e36b6b",
                  y: $source_data.disagree
                }, 
                {
                  name: "Strongly disagree",
                  color: "#e36b6b",
                  y: $source_data.str_disagree
                }, 
                {
                  name: "Prefer not to say",
                  color: "#bdbdbd",
                  y: $source_data.none
                }]
          }]
      });
      }

      $('#exportbep').on('click', function() {
        $selected_prv = $("#prvSelect").val();
        $selected_mun = $("#munSelect").val();
        window.open("exportStats/"+$selected_prv+"/"+$selected_mun, '_blank');
      });
      
      $('#exportcon').on('click', function() {
        $selected_prv = $("#prvSelectConv").val();
        $selected_mun = $("#munSelectConv").val();
        window.open("exportStatsCon/"+$selected_prv+"/"+$selected_mun, '_blank');
      });

      $('#exportbepraw').on('click', function() {
   

        window.open("paymaya/ebinhi/css", '_blank');
      });

      // function loadProvinces(){
      //   $.ajax({
      //     type: 'GET',
      //     url: "{{ route('getIncludedProvinces') }}",
      //     success: function(source){
      //       $('#prSelect option:gt(0)').remove(); 
      //       $counter = 0;
      //       $.each(source, function(){
      //           $("#prvSelect").append('<option value="'+source[$counter]+'">'+source[$counter]+'</option>');
      //           $counter++;
      //       });
      //       $('#munSelect option:gt(0)').remove();
      //     }
      //   });
      // }

      // function loadProvinces() {
      //     $.ajax({
      //         type: 'GET',
      //         url: "{{ route('getIncludedProvinces') }}",
      //         success: function(source) {
      //             if (Array.isArray(source) && source.length > 0) {
      //                 // Clear existing options except the first one
      //                 $('#prvSelect option:gt(0)').remove();

      //                 // Iterate through the source and append new options
      //                 $.each(source, function(index, value) {
      //                     $("#prvSelect").append('<option value="' + value + '">' + value + '</option>');
      //                 });

      //                 // Clear the municipality select options except the first one
      //                 $('#munSelect option:gt(0)').remove();
      //             } else {
      //                 console.log("No provinces returned", source);
      //             }
      //         },
      //         error: function(xhr, status, error) {
      //             console.error("Error loading provinces:", status, error);
      //         }
      //     });
      // }
      function loadProvinces() {
          $.ajax({
              type: 'GET',
              url: "{{ route('getIncludedProvinces') }}",
              success: function(provinces) {
                  // Clear existing options except the first one
                  $('#prvSelect option:gt(0)').remove();

                  // Iterate through the provinces array and append options
                  provinces.forEach(function(province) {
                      $("#prvSelect").append('<option value="' + province.prvCode + '">' + province.province + '</option>');
                  });
              },
              error: function(xhr, status, error) {
                  console.error('Error loading provinces:', status, error);
              }
          });
      }

      // Call loadProvinces function when the page is ready
      $(document).ready(function() {
          loadProvinces();
      });

      function loadMunicipalities(prv) {
          $.ajax({
              type: 'GET',
              url: "{{ url('/getIncludedMunicipality') }}/" + prv,
              success: function(muniArray) {
                // console.log(muniArray);
                  // if (Array.isArray(source) && source.length > 0) {
                      // Clear existing options except the first one
                      $('#munSelect option:gt(0)').remove();

                      muniArray.forEach(function(muni) {
                        $("#munSelect").append('<option value="' + muni.municipality + '">' + muni.municipality + '</option>');
                      });

                          // var totalCount = muniArray[0].count;
                          $('#totalBep').text(muniArray[0].count);
                  // } else {
                      // console.log("No municipalities returned", source);
                  // }
              },
              error: function(xhr, status, error) {
                  console.error("Error loading municipalities:", status, error);
              }
          });
      }
      // function loadMunicipalities(prv) {
      //     $.ajax({
      //         type: 'GET',
      //         url: "{{ url('/getIncludedMunicipality') }}/" + prv,
      //         success: function(muniArray) {
      //           console.log(muniArray);
      //             // if (Array.isArray(source) && source.length > 0) {
      //                 // Clear existing options except the first one
      //                 $('#munSelect option:gt(0)').remove();

      //                 // Iterate through the source and append new options
      //                 // $.each(source, function(index, value) {
      //                 //     $("#munSelect").append('<option value="' + value + '">' + value + '</option>');
      //                 // });
      //                 muniArray.forEach(function(muni) {
      //                   // $("#munSelect").append('<option value="' + muni.prv + '">' + muni.municipality + '</option>');
      //                 });

      //                 // if (muniArray.length > 0) {
      //                 //     let totalCount = muniArray[0].count;
      //                     // $('#totalBep').text(totalCount);
      //                     $('#totalBep').text(muniArray[0].count);
      //                     // console.log('Total count for the first municipality:', totalCount);
      //                     // Do something with the totalCount if needed
      //                 // }
      //             // } else {
      //                 // console.log("No municipalities returned", source);
      //             // }
      //         },
      //         error: function(xhr, status, error) {
      //             console.error("Error loading municipalities:", status, error);
      //         }
      //     });
      // }
      function loadProvincesConv(){
        $.ajax({
          type: 'GET',
          url: "{{ route('getIncludedProvincesConv') }}",
          success: function(source){
            // console.log(source);
            $('#prvSelectConv option:gt(0)').remove(); 
            $counter = 0;
            $.each(source, function(){
                $("#prvSelectConv").append('<option value="'+source[$counter]+'">'+source[$counter]+'</option>');
                $counter++;
            });
            $('#munSelectConv option:gt(0)').remove();
          }
        });
      }
      // Event listener for province select change
      $('#prvSelect').on('change', function() {
          var selected_prv = $(this).val();
          // console.log(selected_prv);
          if (selected_prv && selected_prv !== 'All') {
              loadMunicipalities(selected_prv);
          } else {
              // Clear the municipality select options except the first one
              $('#munSelect option:gt(0)').remove();  
            }
          filterRes();
      });
      // $("#prvSelect").on('change', function() {
      //   $selected_prv = $("#prvSelect").val();
      //   $.ajax({
      //     type: 'GET',
      //     url: "getIncludedMunicipality/"+$selected_prv,
      //     success: function(source){
      //       $('#munSelect option:gt(0)').remove();
      //       filterRes(); 
      //       $counter = 0;
      //       $.each(source, function(){
      //         $("#munSelect").append('<option value="'+source[$counter]+'">'+source[$counter]+'</option>');
      //         $counter++;
      //       });
      //     }
      //   });
      //   filterRes();
      // });

      $("#prvSelectConv").on('change', function() {
        var selected_prv = $("#prvSelectConv").val();
        // console.log(selected_prv);
        $.ajax({
          type: 'GET',
          url: "getIncludedMunicipalityConv/"+selected_prv,
          success: function(source){
            // console.log(source);
            $('#munSelectConv option:gt(0)').remove();
            $('#totalConv').text(source[0].count);
            // filterResConv();
            // $counter = 0;
            // $.each(source, function(){
              source.forEach(function(muni) {
              $("#munSelectConv").append('<option value="'+muni.municipality+'">'+muni.municipality+'</option>');
            });
            filterResConv();

          }
        });
        // filterResConv();
      });

      $("#munSelect").on('change', function() {
        // $selected_prv = $("#munSelect").val();
        // console.log($selec);
        filterRes();
      });

      $("#munSelectConv").on('change', function() {
        filterResConv();
        // console.log(filterResConv());
      });

      // function filterRes(){
      //   $('#filterFetcher').removeClass('gone');
      //   $('#filterFetcher').css('display', 'flex');
      //   $selected_prv = $("#prvSelect").val();
      //   $selected_mun = $("#munSelect").val();
      //   // console.log($selected_prv +' '+$selected_mun);
      //   $.ajax({
      //       type: 'POST',
      //       url: "{{ route('filterLocation') }}",
      //       data: {
      //         _token: "{{ csrf_token() }}",
      //         prv: $selected_prv,
      //         mun: $selected_mun,
      //       },
      //       success: function(source){
      //         // console.log(source["q1"]);
      //         $("#"+source["q1"].id+"_yes").text(source["q1"].yes+"%");
      //         $("#"+source["q1"].id+"_no").text(source["q1"].no+"%");
      //         $("#"+source["q1"].id+"_maybe").text(source["q1"].maybe+"%");

      //         $("#"+source["q2"].id+"_agree").text(source["q2"].agree+"%");
      //         $("#"+source["q2"].id+"_neutral").text(source["q2"].neutral+"%");
      //         $("#"+source["q2"].id+"_disagree").text(source["q2"].disagree+"%");
      //         $("#"+source["q2"].id+"_none").text(source["q2"].none+"%");
              
      //         $("#"+source["q3"].id+"_agree").text(source["q3"].agree+"%");
      //         $("#"+source["q3"].id+"_neutral").text(source["q3"].neutral+"%");
      //         $("#"+source["q3"].id+"_disagree").text(source["q3"].disagree+"%");
      //         $("#"+source["q3"].id+"_none").text(source["q3"].none+"%");

      //         $("#"+source["q4"].id+"_agree").text(source["q4"].agree+"%");
      //         $("#"+source["q4"].id+"_neutral").text(source["q4"].neutral+"%");
      //         $("#"+source["q4"].id+"_disagree").text(source["q4"].disagree+"%");
      //         $("#"+source["q4"].id+"_none").text(source["q4"].none+"%");

      //         $("#"+source["q5"].id+"_agree").text(source["q5"].agree+"%");
      //         $("#"+source["q5"].id+"_neutral").text(source["q5"].neutral+"%");
      //         $("#"+source["q5"].id+"_disagree").text(source["q5"].disagree+"%");
      //         $("#"+source["q5"].id+"_none").text(source["q5"].none+"%");

      //         $("#"+source["q6"].id+"_agree").text(source["q6"].agree+"%");
      //         $("#"+source["q6"].id+"_neutral").text(source["q6"].neutral+"%");
      //         $("#"+source["q6"].id+"_disagree").text(source["q6"].disagree+"%");
      //         $("#"+source["q6"].id+"_none").text(source["q6"].none+"%");

      //         $("#"+source["q7"].id+"_agree").text(source["q7"].agree+"%");
      //         $("#"+source["q7"].id+"_neutral").text(source["q7"].neutral+"%");
      //         $("#"+source["q7"].id+"_disagree").text(source["q7"].disagree+"%");
      //         $("#"+source["q7"].id+"_none").text(source["q7"].none+"%");

      //         $("#"+source["q8"].id+"_agree").text(source["q8"].agree+"%");
      //         $("#"+source["q8"].id+"_neutral").text(source["q8"].neutral+"%");
      //         $("#"+source["q8"].id+"_disagree").text(source["q8"].disagree+"%");
      //         $("#"+source["q8"].id+"_none").text(source["q8"].none+"%");

      //         $("#"+source["q9"].id+"_agree").text(source["q9"].agree+"%");
      //         $("#"+source["q9"].id+"_neutral").text(source["q9"].neutral+"%");
      //         $("#"+source["q9"].id+"_disagree").text(source["q9"].disagree+"%");
      //         $("#"+source["q9"].id+"_none").text(source["q9"].none+"%");

      //         $("#"+source["q10"].id+"_agree").text(source["q10"].agree+"%");
      //         $("#"+source["q10"].id+"_neutral").text(source["q10"].neutral+"%");
      //         $("#"+source["q10"].id+"_disagree").text(source["q10"].disagree+"%");
      //         $("#"+source["q10"].id+"_none").text(source["q10"].none+"%");

      //         $("#"+source["q11"].id+"_agree").text(source["q11"].agree+"%");
      //         $("#"+source["q11"].id+"_neutral").text(source["q11"].neutral+"%");
      //         $("#"+source["q11"].id+"_disagree").text(source["q11"].disagree+"%");
      //         $("#"+source["q11"].id+"_none").text(source["q11"].none+"%");

      //         $("#"+source["q12"].id+"_agree").text(source["q12"].agree+"%");
      //         $("#"+source["q12"].id+"_neutral").text(source["q12"].neutral+"%");
      //         $("#"+source["q12"].id+"_disagree").text(source["q12"].disagree+"%");
      //         $("#"+source["q12"].id+"_none").text(source["q12"].none+"%");

      //         $("#"+source["q15"].id+"_yes").text(source["q15"].yes+"%");
      //         $("#"+source["q15"].id+"_no").text(source["q15"].no+"%");
      //         $("#"+source["q15"].id+"_maybe").text(source["q15"].maybe+"%");

      //         $("#totalBep").text(source["total"]);



      //         $('#filterFetcher').addClass('gone');

      //         setTimeout(() => {
      //           $('#filterFetcher').css('display', 'none');
      //         }, 1000);
      //       }
      //     });
      // }

      function filterRes(){
        $('#filterFetcher').removeClass('gone');
        $('#filterFetcher').css('display', 'flex');
        selected_prv = $("#prvSelect").val();
        selected_mun = $("#munSelect").val();
        // console.log(selected_prv + selected_mun);
        $.ajax({
            type: 'POST',
            url: "{{ route('filterLocation') }}",
            data: {
              _token: "{{ csrf_token() }}",
              prv: selected_prv,
              mun: selected_mun,
            },
            success: function(source){
              // console.log(source);

              Object.keys(source).forEach(key => {
                // console.log(source);
                 let str = "";
                 let counter_percent = 0.0;
                Object.keys(source[key].options).forEach(key2 => {
                  str = source[key].options[key2].count + " / " + source[key].total_response ;
                  $(`#bep_question_${key}_${key2}`).empty().append(str);
                  counter_percent = (parseInt(source[key].options[key2].count) / parseInt(source[key].total_response)) * 100;
                  $(`#bep_bar_${key}_${key2}`).css("width", counter_percent + "%");
                  
                  $('#totalBep').text(source[key].total_response);
                });
              });

              $('#filterFetcher').addClass('gone');

              setTimeout(() => {
                $('#filterFetcher').css('display', 'none');
              }, 1000);
            }
          });
      }
      function filterResConv(){
        $('#filterFetcherConv').removeClass('gone');
        $('#filterFetcherConv').css('display', 'flex');
        $selected_prv = $("#prvSelectConv").val();
        $selected_mun = $("#munSelectConv").val();
        // console.log($selected_prv +' '+$selected_mun);
        $.ajax({
            type: 'POST',
            url: "{{ route('filterLocationConv') }}",
            data: {
              _token: "{{ csrf_token() }}",
              prv: $selected_prv,
              mun: $selected_mun,
            },
            success: function(source){
              // console.log(source);
              Object.keys(source).forEach(key => {
                 let str = "";
                 let counter_percent = 0.0;
                Object.keys(source[key].options).forEach(key2 => {
                  $('#totalConv').text(source[key].total_response);
                  str = source[key].options[key2].count + " / " + source[key].total_response ;
                  $(`#con_question_${key}_${key2}`).empty().append(str);
                  counter_percent = (parseInt(source[key].options[key2].count) / parseInt(source[key].total_response)) * 100;
                  $(`#con_bar_${key}_${key2}`).css("width", counter_percent + "%");
                });
              });
              
              $('#filterFetcherConv').addClass('gone');

              setTimeout(() => {
                $('#filterFetcherConv').css('display', 'none');
              }, 1000);
              
            }
          });
      }
      // function filterResConv(){
      //   $('#filterFetcherConv').removeClass('gone');
      //   $('#filterFetcherConv').css('display', 'flex');
      //   $selected_prv = $("#prvSelectConv").val();
      //   $selected_mun = $("#munSelectConv").val();
      //   console.log($selected_prv +' '+$selected_mun);
      //   $.ajax({
      //       type: 'POST',
      //       url: "{{ route('filterLocationConv') }}",
      //       data: {
      //         _token: "{{ csrf_token() }}",
      //         prv: $selected_prv,
      //         mun: $selected_mun,
      //       },
      //       success: function(source){
      //         console.log(source);

      //         $("#totalConv").text(source["total"]);

      //         $("#"+source["q1"].id+"_yes_2").text(source["q1"].yes_2+"%");
      //         $("#"+source["q1"].id+"_yes_1").text(source["q1"].yes_1+"%");
      //         $("#"+source["q1"].id+"_neutral").text(source["q1"].neutral+"%");
      //         $("#"+source["q1"].id+"_no_1").text(source["q1"].no_1+"%");
      //         $("#"+source["q1"].id+"_no_2").text(source["q1"].no_2+"%");
      //         $("#"+source["q1"].id+"_none").text(source["q1"].none+"%");
              
      //         $("#"+source["q2"].id+"_yes_2").text(source["q2"].yes_2+"%");
      //         $("#"+source["q2"].id+"_yes_1").text(source["q2"].yes_1+"%");
      //         $("#"+source["q2"].id+"_neutral").text(source["q2"].neutral+"%");
      //         $("#"+source["q2"].id+"_no_1").text(source["q2"].no_1+"%");
      //         $("#"+source["q2"].id+"_no_2").text(source["q2"].no_2+"%");
      //         $("#"+source["q2"].id+"_none").text(source["q2"].none+"%");
              
      //         $("#"+source["q3"].id+"_yes_2").text(source["q3"].yes_2+"%");
      //         $("#"+source["q3"].id+"_yes_1").text(source["q3"].yes_1+"%");
      //         $("#"+source["q3"].id+"_neutral").text(source["q3"].neutral+"%");
      //         $("#"+source["q3"].id+"_no_1").text(source["q3"].no_1+"%");
      //         $("#"+source["q3"].id+"_no_2").text(source["q3"].no_2+"%");
      //         $("#"+source["q3"].id+"_none").text(source["q3"].none+"%");
              
      //         $('#filterFetcherConv').addClass('gone');

      //         setTimeout(() => {
      //           $('#filterFetcherConv').css('display', 'none');
      //         }, 1000);
      //       }
      //     });
      // }

      function showEasterEgg(){
        setTimeout(() => {
          $(".easteregg").addClass("showeasteregg");
          setTimeout(() => {
            $(".easteregg").removeClass("showeasteregg");
          }, 5000);
        }, 5000);
      }
    </script>
@endpush