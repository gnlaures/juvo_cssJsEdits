const DetectElement = function(elementToDetect) {
  this.top = elementToDetect.offsetTop;
  this.left = elementToDetect.offsetLeft;
  this.width = elementToDetect.offsetWidth;
  this.height = elementToDetect.offsetHeight;
  this.elementToDetect = elementToDetect;
}
DetectElement.prototype = {
  getPosition: function() {
    while (this.elementToDetect.offsetParent) {
      this.elementToDetect = this.elementToDetect.offsetParent;
      this.top += this.elementToDetect.offsetTop - 150;
      this.left += this.elementToDetect.offsetLeft;
    }
    return (this.top < (window.pageYOffset + window.innerHeight) && this.left < (window.pageXOffset + window.innerWidth) && ((this.top + this.height) > window.pageYOffset) || ((this.top + this.height) <= window.pageYOffset) && (this.left + this.width) > window.pageXOffset);
  }
}