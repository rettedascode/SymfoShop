FROM nginx:alpine

# Create nginx cache directory
RUN mkdir -p /var/cache/nginx

# Expose port 80
EXPOSE 80

# Start nginx
CMD ["nginx", "-g", "daemon off;"] 