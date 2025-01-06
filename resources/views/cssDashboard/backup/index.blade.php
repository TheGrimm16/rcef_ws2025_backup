<?php $qr_side = "active"; $qr_home="active"?>

@extends('layouts.index')

@section('styles')
  <link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/assets/iCheck/skins/flat/green.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/daterangepicker.css') }}">
  {{-- <link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}"> --}}
  <style>
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
      grid-template-columns: 3fr 2fr 3fr;
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

    .card-items{
      position: relative;
      display: grid;
      grid-template-columns: 3fr 4fr;
      gap: 2rem;
    }

    .card-item > div{
      border-radius: 10px;
      padding: 2rem;
    }

    .mini_grid > div{
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      border-left: 1px rgb(200, 200, 200) solid;
    } 

    .mini_grid > div:nth-of-type(1){
      border-left: none;
    }

    .statsq1 > div{
      padding: 0.2rem;
      display: flex;
      align-items: center;
      background: #e4e4e4;
      border-radius: 5px;
      margin-bottom: 0.2rem;
      cursor: pointer;
      position: relative;
      /* justify-content: center; */
    }

    .q1_y::before{
      content: attr(data-percentage);
      color: white;
      position: absolute;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgb(56, 136, 56);
      border-radius: 5px;
      top: 0;
      left: 0;
      bottom: 0;
      width: 64%;
    }
    .q1_n::before{
      content: attr(data-percentage);
      color: white;
      position: absolute;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgb(217, 87, 87);
      border-radius: 5px;
      top: 0;
      left: 0;
      bottom: 0;
      width: 16%;
    }
    .q1_m::before{
      content: attr(data-percentage);
      color: white;
      position: absolute;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgb(130, 103, 103);
      border-radius: 5px;
      top: 0;
      left: 0;
      bottom: 0;
      width: 20%;
    }
    .q1_w::before{
      content: attr(data-percentage);
      color: white;
      position: absolute;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgb(180, 180, 180);
      border-radius: 5px;
      top: 0;
      left: 0;
      bottom: 0;
      width: 20%;
    }
    .questions > div:nth-last-of-type(1){
      margin-bottom: 1rem;
    }

    .exploded_view{
      width: 100%;
      background: white;
      border-radius: 1rem;
      min-height: 10rem;
      max-height: 30rem;
      position: relative;
    }

    .bold{
      font-weight: 700;
    }

    .placeholder_empty, .placeholder_empty2{
      display: flex;
      align-items: center;
      justify-content: center;
      width: 100%;
      min-height: 10rem;
      height: 100%;
      color: rgb(186, 186, 186);
    }

    .questions > div{
      background-color: transparent;
      transition: background-color 0.2s ease-in-out;
      margin-bottom: 1rem;
    }

    .questions > div:hover{
      cursor: pointer;
      background-color: #e4e4e4;
      /* transition: background-color 0.5s ease-in-out; */
    }

    .actual_exploded_view{
      padding: 3rem 2rem;
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
  </style>
@endsection

@section('content')
<div class="__content" style="position: relative; padding-top: 8vh;">
  {{-- <div class="trychartt shadow-md" style="width: 30rem; background: #808080;"> 
    <div class="bar q1y yes" >Yes</div>
    <div class="bar q1m" >Maybe</div>
    <div class="bar q1n no" >No</div>
  </div> --}}
  <div class="title">
    CSS Binhi e-Padala & Conventional Dashboard
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
    </div>
    <div class="three banner_item">

    </div>  
  </div>

  <div class="__contents mt-4">
    <div class="section bep_content slideFromRight" active>
      <strong>Binhi e-Padala Seeds Distribution Survey Results</strong>
      <div class="actual_content">
        {{-- <div class="title">
          Statistics
        </div> --}}
        <div class="card-items">
          <div class="card-item">

            <div class="card-1 shadow-md">
              <h3><strong>Total Respondents</strong></h3>
              <div class="mini_grid" style="display: grid; grid-template-columns: 1fr 1fr 1fr;">
                <div class="total" style="font-size: 1.4rem">
                  <h1><strong>567</strong></h1>
                  Overall
              </div>
                <div class="male" style="font-size: 1.4rem">
                  <h1><strong>46.59%</strong></h1> 
                  Male
                </div>
                <div class="female" style="font-size: 1.4rem">
                  <h1><strong>54.41%</strong></h1> 
                  Female
                </div>
              </div>
            </div>

            <div class="card-1 shadow-md mt-4">
              <h3><strong>Age Brackets</strong></h3>
              <div class="mini_grid" style="display: grid; grid-template-columns: 1fr 1fr 1fr;">
                <div class="total" style="font-size: 1.4rem"><h1><strong>20%</strong></h1>
                  Age 18-29 y/o
                </div>
                <div class="male" style="font-size: 1.4rem"><h1><strong>49.5%</strong></h1> 
                  Age 30-59 y/o
                </div>
                <div class="female" style="font-size: 1.4rem"><h1><strong>31.5%</strong></h1> 
                  Age 60+ y/o
                </div>
              </div>
            </div>

          </div>
          <div class="card-item questions" style="height: 55vh; overflow-y: auto;">

            <div id="sample_listener" class="card-1 shadow-md">
              <div class="_heading" style="display: grid; grid-template-columns: 6fr 1fr; padding: 0;">
                <h4><strong>Gusto mo bang magpatuloy sa Binhi e-Padala na sistema ng pamimigay ng binhi?</strong></h4>
                <div class="q1_percentage" style="display: flex; align-items: center; flex-direction: column; justify-content: center; border-left: 1px solid rgb(210, 210, 210);">
                  <h3><strong>64%</strong></h3>
                  <span style="font-size: 1rem;">answered Yes.</span>
                </div>
              </div>
              <div class="stats_grid" style="display: grid; grid-template-columns: 9fr 1fr; gap: 1rem;">
                <div class="statsq1" style="width: 100%; font-size: 1rem; display: grid; grid-template-columns: 1fr; margin-top: 1rem;">
                  <div class="q1_y" data-percentage="64%">64%</div>
                  <div class="q1_n" data-percentage="16%">16%</div>
                  <div class="q1_m" data-percentage="20%">20%</div>
                </div>
                <div class="statsq1label" style="width: 100%; font-size: 1rem; display: grid; grid-template-columns: 1fr; margin-top: 1rem; place-items: center end;">
                  <div class="q1y_label">Yes</div>
                  <div class="q1n_label">No</div>
                  <div class="q1m_label">No Answer</div>
                </div>
              </div>
            </div>

            <div class="card-1 shadow-md">
              <div class="_heading" style="display: grid; grid-template-columns: 6fr 1fr; padding: 0;">
                <h4><strong>Nalaman ko ng mas maaga ang iskedyul dahil sa text.</strong></h4>
                <div class="q1_percentage" style="display: flex; align-items: center; flex-direction: column; justify-content: center; border-left: 1px solid rgb(210, 210, 210);">
                  <h3><strong>44%</strong></h3>
                  <span style="font-size: 1rem;">agreed.</span>
                </div>
              </div>
              <div class="stats_grid" style="display: grid; grid-template-columns: 9fr 1fr; gap: 1rem;">
                <div class="statsq1" style="width: 100%; font-size: 1rem; display: grid; grid-template-columns: 1fr; margin-top: 1rem;">
                  <div class="q1_y" data-percentage="44%">44%</div>
                  <div class="q1_n" data-percentage="16%">16%</div>
                  <div class="q1_m" data-percentage="20%">20%</div>
                  <div class="q1_w" data-percentage="20%">20%</div>
                </div>
                <div class="statsq1label" style="width: 100%; font-size: 1rem; display: grid; grid-template-columns: 1fr; margin-top: 1rem; place-items: center end;">
                  <div class="q1y_label">Agree</div>
                  <div class="q1n_label">Disagree</div>
                  <div class="q1m_label">Neutral</div>
                  <div class="q1w_label">No Answer</div>
                </div>
              </div>
            </div>
            <div class="card-1 shadow-md">
              <div class="_heading" style="display: grid; grid-template-columns: 6fr 1fr; padding: 0;">
                <h4><strong>Mas tugma sa oras ko ang iskedyul ng pamimigay ng binhi ngayon.</strong></h4>
                <div class="q1_percentage" style="display: flex; align-items: center; flex-direction: column; justify-content: center; border-left: 1px solid rgb(210, 210, 210);">
                  <h3><strong>64%</strong></h3>
                  <span style="font-size: 1rem;">answered Yes.</span>
                </div>
              </div>
              <div class="stats_grid" style="display: grid; grid-template-columns: 9fr 1fr; gap: 1rem;">
                <div class="statsq1" style="width: 100%; font-size: 1rem; display: grid; grid-template-columns: 1fr; margin-top: 1rem;">
                  <div class="q1_y" data-percentage="64%">64%</div>
                  <div class="q1_n" data-percentage="16%">16%</div>
                  <div class="q1_m" data-percentage="20%">20%</div>
                </div>
                <div class="statsq1label" style="width: 100%; font-size: 1rem; display: grid; grid-template-columns: 1fr; margin-top: 1rem; place-items: center end;">
                  <div class="q1y_label">Yes</div>
                  <div class="q1n_label">No</div>
                  <div class="q1m_label">No Answer</div>
                </div>
              </div>
            </div>
            <div class="card-1 shadow-md">
              <div class="_heading" style="display: grid; grid-template-columns: 6fr 1fr; padding: 0;">
                <h4><strong>Mas malapit ang pinagkuhanan ko ng binhi ngayon.</strong></h4>
                <div class="q1_percentage" style="display: flex; align-items: center; flex-direction: column; justify-content: center; border-left: 1px solid rgb(210, 210, 210);">
                  <h3><strong>64%</strong></h3>
                  <span style="font-size: 1rem;">answered Yes.</span>
                </div>
              </div>
              <div class="stats_grid" style="display: grid; grid-template-columns: 9fr 1fr; gap: 1rem;">
                <div class="statsq1" style="width: 100%; font-size: 1rem; display: grid; grid-template-columns: 1fr; margin-top: 1rem;">
                  <div class="q1_y" data-percentage="64%">64%</div>
                  <div class="q1_n" data-percentage="16%">16%</div>
                  <div class="q1_m" data-percentage="20%">20%</div>
                </div>
                <div class="statsq1label" style="width: 100%; font-size: 1rem; display: grid; grid-template-columns: 1fr; margin-top: 1rem; place-items: center end;">
                  <div class="q1y_label">Yes</div>
                  <div class="q1n_label">No</div>
                  <div class="q1m_label">No Answer</div>
                </div>
              </div>
            </div>

          </div>
        </div>
        <div class="divider" style="border-bottom: 1px solid rgb(177, 177, 177); margin: 2rem 0rem 2rem 0rem"></div>
        <div class="exploded_view">
          <div class="placeholder_empty">
            Select one of the questions.
          </div>
          <div class="actual_exploded_view slideFromRight" style="display: none;">
            <h4><strong>Gusto mo bang magpatuloy sa Binhi e-Padala na sistema ng pamimigay ng binhi?</strong></h4>
            <div class="exploded_stats" style="display: flex; align-items: center; justify-content: space-evenly">
              <div class="option" style="display: grid; place-items: center">
                <h1><strong>64%</strong></h1>
                <span style="font-size: 1.2rem;">Approximately <span class="bold">298</span> farmer(s) said <span class="bold">Yes</span>.</span>
              </div>
              <div class="option" style="display: grid; place-items: center">
                <h1><strong>16%</strong></h1>
                <span style="font-size: 1.2rem;">Approximately <span class="bold">98</span> farmer(s) said <span class="bold">No</span>.</span>
              </div>
              <div class="option" style="display: grid; place-items: center">
                <h1><strong>20%</strong></h1>
                <span style="font-size: 1.2rem;">Approximately <span class="bold">124</span> farmer(s) had <span class="bold">no answer</span>.</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="section con_content slideFromLeft">
      <strong>Conventional Seeds Distribution Survey Results</strong>
      <div class="actual_content">
        <div class="card-items">
          <div class="card-item">

            <div class="card-1 shadow-md">
              <h3><strong>Total Respondents</strong></h3>
              <div class="mini_grid" style="display: grid; grid-template-columns: 1fr 1fr 1fr;">
                <div class="total" style="font-size: 1.4rem">
                  <h1><strong>1,231</strong></h1>
                  Overall
              </div>
                <div class="male" style="font-size: 1.4rem">
                  <h1><strong>54.12%</strong></h1> 
                  Male
                </div>
                <div class="female" style="font-size: 1.4rem">
                  <h1><strong>46.88%</strong></h1> 
                  Female
                </div>
              </div>
            </div>

            <div class="card-1 shadow-md mt-4">
              <h3><strong>Age Brackets</strong></h3>
              <div class="mini_grid" style="display: grid; grid-template-columns: 1fr 1fr 1fr;">
                <div class="total" style="font-size: 1.4rem"><h1><strong>15%</strong></h1>
                  Age 18-29 y/o
                </div>
                <div class="male" style="font-size: 1.4rem"><h1><strong>64.5%</strong></h1> 
                  Age 30-59 y/o
                </div>
                <div class="female" style="font-size: 1.4rem"><h1><strong>21.5%</strong></h1> 
                  Age 60+ y/o
                </div>
              </div>
            </div>

          </div>
          <div class="card-item questions" style="height: 55vh; overflow-y: auto;">

            <div id="sample_listener" class="card-1 shadow-md">
              <div class="_heading" style="display: grid; grid-template-columns: 6fr 1fr; padding: 0;">
                <h4><strong>Maayos ba ang proseso ng pagtala?</strong></h4>
                <div class="q1_percentage" style="display: flex; align-items: center; flex-direction: column; justify-content: center; border-left: 1px solid rgb(210, 210, 210);">
                  <h3><strong>64%</strong></h3>
                  <span style="font-size: 1rem;">answered Yes.</span>
                </div>
              </div>
              <div class="stats_grid" style="display: grid; grid-template-columns: 9fr 1fr; gap: 1rem;">
                <div class="statsq1" style="width: 100%; font-size: 1rem; display: grid; grid-template-columns: 1fr; margin-top: 1rem;">
                  <div class="q1_y" data-percentage="64%">64%</div>
                  <div class="q1_n" data-percentage="16%">16%</div>
                  <div class="q1_m" data-percentage="20%">20%</div>
                </div>
                <div class="statsq1label" style="width: 100%; font-size: 1rem; display: grid; grid-template-columns: 1fr; margin-top: 1rem; place-items: center end;">
                  <div class="q1y_label">Yes</div>
                  <div class="q1n_label">No</div>
                  <div class="q1m_label">No Answer</div>
                </div>
              </div>
            </div>

            <div class="card-1 shadow-md">
              <div class="_heading" style="display: grid; grid-template-columns: 6fr 1fr; padding: 0;">
                <h4><strong>Maayos ba at madaling intindihin ang <i>technical briefing?</i></strong></h4>
                <div class="q1_percentage" style="display: flex; align-items: center; flex-direction: column; justify-content: center; border-left: 1px solid rgb(210, 210, 210);">
                  <h3><strong>44%</strong></h3>
                  <span style="font-size: 1rem;">agreed.</span>
                </div>
              </div>
              <div class="stats_grid" style="display: grid; grid-template-columns: 9fr 1fr; gap: 1rem;">
                <div class="statsq1" style="width: 100%; font-size: 1rem; display: grid; grid-template-columns: 1fr; margin-top: 1rem;">
                  <div class="q1_y" data-percentage="44%">44%</div>
                  <div class="q1_n" data-percentage="16%">16%</div>
                  <div class="q1_m" data-percentage="20%">20%</div>
                  <div class="q1_w" data-percentage="20%">20%</div>
                </div>
                <div class="statsq1label" style="width: 100%; font-size: 1rem; display: grid; grid-template-columns: 1fr; margin-top: 1rem; place-items: center end;">
                  <div class="q1y_label">Agree</div>
                  <div class="q1n_label">Disagree</div>
                  <div class="q1m_label">Neutral</div>
                  <div class="q1w_label">No Answer</div>
                </div>
              </div>
            </div>

            <div class="card-1 shadow-md">
              <div class="_heading" style="display: grid; grid-template-columns: 6fr 1fr; padding: 0;">
                <h4><strong>Maayos at mabilis ba ang pagkuha ng iyong binhi?</strong></h4>
                <div class="q1_percentage" style="display: flex; align-items: center; flex-direction: column; justify-content: center; border-left: 1px solid rgb(210, 210, 210);">
                  <h3><strong>64%</strong></h3>
                  <span style="font-size: 1rem;">answered Yes.</span>
                </div>
              </div>
              <div class="stats_grid" style="display: grid; grid-template-columns: 9fr 1fr; gap: 1rem;">
                <div class="statsq1" style="width: 100%; font-size: 1rem; display: grid; grid-template-columns: 1fr; margin-top: 1rem;">
                  <div class="q1_y" data-percentage="64%">64%</div>
                  <div class="q1_n" data-percentage="16%">16%</div>
                  <div class="q1_m" data-percentage="20%">20%</div>
                </div>
                <div class="statsq1label" style="width: 100%; font-size: 1rem; display: grid; grid-template-columns: 1fr; margin-top: 1rem; place-items: center end;">
                  <div class="q1y_label">Yes</div>
                  <div class="q1n_label">No</div>
                  <div class="q1m_label">No Answer</div>
                </div>
              </div>
            </div>

          </div>
        </div>
        <div class="divider" style="border-bottom: 1px solid rgb(177, 177, 177); margin: 2rem 0rem 2rem 0rem"></div>
        <div class="exploded_view">
          <div class="placeholder_empty2">
            Select one of the questions.
          </div>
          <div class="actual_exploded_view2 slideFromRight" style="display: none;">
            <h4><strong>Gusto mo bang magpatuloy sa Binhi e-Padala na sistema ng pamimigay ng binhi?</strong></h4>
            <div class="exploded_stats" style="display: flex; align-items: center; justify-content: space-evenly">
              <div class="option" style="display: grid; place-items: center">
                <h1><strong>64%</strong></h1>
                <span style="font-size: 1.2rem;">Approximately <span class="bold">298</span> farmer(s) said <span class="bold">Yes</span>.</span>
              </div>
              <div class="option" style="display: grid; place-items: center">
                <h1><strong>16%</strong></h1>
                <span style="font-size: 1.2rem;">Approximately <span class="bold">98</span> farmer(s) said <span class="bold">No</span>.</span>
              </div>
              <div class="option" style="display: grid; place-items: center">
                <h1><strong>20%</strong></h1>
                <span style="font-size: 1.2rem;">Approximately <span class="bold">124</span> farmer(s) had <span class="bold">no answer</span>.</span>
              </div>
            </div>
          </div>
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
      $yes = 64;
      $no = 16;
      $maybe = 20;
      $total = ($yes + $no) + $maybe;
      
      $('.q1y').css('width', (($yes/$total)*100)+'%');
      $('.q1m').css('width', (($maybe/$total)*100)+'%');
      $('.q1n').css('width', (($no/$total)*100)+'%');


      //CHANGE SECTIONS
      $('#bep_section').on('click', function() {
        $('#con_section').removeAttr('selected');
        $('#bep_section').attr('selected', '');
        $('.bep_content').attr('active', '');
        $('.con_content').removeAttr('active');
      });

      $('#con_section').on('click', function() {
        $('#bep_section').removeAttr('selected');
        $('#con_section').attr('selected', '');
        $('.con_content').attr('active', '');
        $('.bep_content').removeAttr('active');
      });
      //END CHANGE SECTIONS

      //START LISTENERS

      $("#sample_listener").on('click', function() {
        $(".placeholder_empty").css('display', 'none');
        $(".actual_exploded_view").css('display', 'block');
      });

      //END LISTENERS


    </script>
@endpush