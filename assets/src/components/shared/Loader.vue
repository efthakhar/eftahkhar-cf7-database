
<template>
<div class="loader-container">
    <span class="loader"></span>
</div>
</template>

<style>


.loader-container{
    width: 100% !important;
    height: 100% !important;
    display: flex;
    justify-content: center;
    align-items: center;
    padding-top: 60px;
}

.loader {
  color: #FFF;
  position: relative;
  font-size: 11px;
  background: #FFF;
  animation: escaleY 1s infinite ease-in-out;
  width: 1em;
  height: 4em;
  animation-delay: -0.16s;
}
.loader:before,
.loader:after {
  content: '';
  position: absolute;
  top: 0;
  left: 2em;
  background: #FFF;
  width: 1em;
  height: 4em;
  animation: escaleY 1s infinite ease-in-out;
}
.loader:before {
  left: -2em;
  animation-delay: -0.32s;
}

@keyframes escaleY {
  0%, 80%, 100% {
    box-shadow: 0 0;
    height: 4em;
  }
  40% {
    box-shadow: 0 -2em;
    height: 5em;
  }
}


</style> 