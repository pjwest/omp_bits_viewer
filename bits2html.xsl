<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet version="1.0" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xlink="http://www.w3.org/1999/xlink"
	>

<xsl:template match="/">
  <html>
     <body>
	     <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500"/>
	<script src="https://unpkg.com/mirador@latest/dist/mirador.min.js"></script>

        <h2>BITS XML</h2>
	<xsl:apply-templates select="book"/>
        <h3>end</h3>
	<ol>
	   <xsl:apply-templates select="//fig"/>
	</ol>
     </body>
  </html>
</xsl:template>

<xsl:template match="book">
	<h2>BOOK</h2>
	<p>(language <xsl:value-of select="@xml:lang"/>)</p>
	<xsl:apply-templates select="book-meta"/>
	<xsl:apply-templates select="book-body"/>
</xsl:template>

<xsl:template match="book-meta">
	<h3>BOOK Metadata</h3>
	<h4><xsl:value-of select="book-title-group"/></h4>
	<p>ISBN: <xsl:value-of select="isbn"/></p>
	<p>Published: <xsl:value-of select="pub-date"/></p>
	<xsl:for-each select="book-id">
	   <p><xsl:value-of select="@book-id-type"/>: <xsl:value-of select="."/></p>
        </xsl:for-each>
</xsl:template>

<xsl:template match="book-body">
	<h3>BOOK Body</h3>
	<xsl:apply-templates select="book-part"/>
</xsl:template>

<xsl:template match="book-part">
	<h3>BOOK part</h3>
	<xsl:apply-templates select="body/sec"/>
</xsl:template>

<xsl:template match="sec">
	
   <h3><xsl:value-of select="title"/></h3>

   <xsl:for-each select="fig">
      <xsl:for-each select="graphic">
         <xsl:variable name="fig_id">
            <xsl:value-of select="@id"/>
         </xsl:variable>
         <div>
            <a name="{$fig_id}">
		      <div>Orientation: <xsl:value-of select="@orientation"/></div>
		      <div>Position: <xsl:value-of select="@position"/></div>
		      <div>Link: <xsl:value-of select="@xlink:href"/></div>
	    </a>
	    <div id="mirador_{$fig_id}" style="width: 800px; height: 600px; display: none;">
	    </div>
         
            <script type="text/javascript">
		var figId = '<xsl:value-of select="$fig_id" />';
		var divId = "mirador_"+figId;
	var mirador = Mirador.viewer({
		    "id": divId,
	  "manifests": {
	    "https://iiif.lib.harvard.edu/manifests/drs:48309543": {
	      "provider": "Harvard University"
	    }
	  },
	  "windows": [
	    {
	      "loadedManifest": "https://iiif.lib.harvard.edu/manifests/drs:48309543",
	      "canvasIndex": 2,
	      "thumbnailNavigationPosition": 'far-bottom'
	    }
	  ]
	});
	</script>


         </div>
			  
      </xsl:for-each>
      <div><xsl:value-of select="caption"/></div>
   </xsl:for-each>
	
   <xsl:for-each select="p">
      <p><xsl:value-of select="."/></p>
   </xsl:for-each>
   <xsl:apply-templates select="sec"/>
</xsl:template>



<xsl:template match="fig">
   <xsl:variable name="fig_caption">
      <xsl:value-of select="caption"/>
   </xsl:variable>
   <xsl:for-each select="graphic">
      <xsl:variable name="fig_id">
         #<xsl:value-of select="@id"/>
      </xsl:variable>

      <li>
         <a href="{$fig_id}"> <xsl:value-of select="$fig_caption"/> </a>
      </li>
   </xsl:for-each>
</xsl:template>



</xsl:stylesheet>

